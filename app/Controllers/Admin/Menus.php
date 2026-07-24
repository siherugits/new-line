<?php

namespace App\Controllers\Admin;

use App\Models\MenuModel;
use App\Models\RoleModel;

class Menus extends BaseAdminController
{
    private function roleChoices(): array
    {
        return (new RoleModel())->orderBy('title', 'ASC')->findAll();
    }

    /**
     * Flat, indented list of menus usable as a parent.
     *
     * Rules to keep the tree at most 3 levels and acyclic:
     *  - a menu cannot be its own parent, nor a descendant of itself;
     *  - a menu already at level 3 cannot be a parent (would create level 4).
     *
     * @return list<array{id:int,label:string}>
     */
    private const MAX_DEPTH = 3;

    private function parentChoices(?int $excludeId = null): array
    {
        $all = (new MenuModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();

        // index children by parent id
        $byParent = [];
        foreach ($all as $m) {
            $byParent[(int) ($m['parent_id'] ?? 0)][] = $m;
        }

        // collect the excluded subtree (self + all descendants)
        $excluded = [];
        if ($excludeId !== null) {
            $stack = [$excludeId];
            while ($stack) {
                $id            = array_pop($stack);
                $excluded[$id] = true;
                foreach ($byParent[$id] ?? [] as $child) {
                    $stack[] = (int) $child['id'];
                }
            }
        }

        $choices = [];
        $walk    = function (int $parentId, int $depth) use (&$walk, &$byParent, &$excluded, &$choices): void {
            foreach ($byParent[$parentId] ?? [] as $m) {
                $id = (int) $m['id'];
                if (isset($excluded[$id])) {
                    continue;
                }
                // depth is 1-based level of THIS item; only levels < MAX_DEPTH may be parents
                if ($depth < self::MAX_DEPTH) {
                    $choices[] = [
                        'id'    => $id,
                        'label' => str_repeat('— ', $depth - 1) . $m['title'],
                    ];
                }
                $walk($id, $depth + 1);
            }
        };
        $walk(0, 1);

        return $choices;
    }

    public function index(): string
    {
        $menus = (new MenuModel())->orderBy('parent_id', 'ASC')->orderBy('sort_order', 'ASC')->findAll();

        // map id -> title for showing parent name
        $titles = [];
        foreach ($menus as $m) {
            $titles[$m['id']] = $m['title'];
        }

        return $this->render('admin/menus/index', ['menus' => $menus, 'titles' => $titles], 'Menus');
    }

    public function new(): string
    {
        return $this->render('admin/menus/form', [
            'menu'        => null,
            'roles'       => $this->roleChoices(),
            'parents'     => $this->parentChoices(),
            'menuRoleIds' => [],
        ], 'New Menu');
    }

    public function create()
    {
        $model = new MenuModel();
        if (! $this->validate($model->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($err = $this->parentDepthError($model, null, $this->parentIdFromPost())) {
            return redirect()->back()->withInput()->with('error', $err);
        }
        $model->insert($this->payload());
        $id = $model->getInsertID();
        $model->syncAccess($id, array_map('intval', (array) $this->request->getPost('roles')));

        return redirect()->to('admin/menus')->with('message', 'Menu created.');
    }

    public function edit(int $id)
    {
        $model = new MenuModel();
        $menu  = $model->find($id);
        if (! $menu) {
            return redirect()->to('admin/menus')->with('error', 'Menu not found.');
        }

        return $this->render('admin/menus/form', [
            'menu'        => $menu,
            'roles'       => $this->roleChoices(),
            'parents'     => $this->parentChoices($id),
            'menuRoleIds' => array_map('intval', array_column($model->accessRoleIds($id), 'role_id')),
        ], 'Edit Menu');
    }

    public function update(int $id)
    {
        $model = new MenuModel();
        if (! $model->find($id)) {
            return redirect()->to('admin/menus')->with('error', 'Menu not found.');
        }
        if (! $this->validate($model->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($err = $this->parentDepthError($model, $id, $this->parentIdFromPost())) {
            return redirect()->back()->withInput()->with('error', $err);
        }
        $model->update($id, $this->payload());
        $model->syncAccess($id, array_map('intval', (array) $this->request->getPost('roles')));

        return redirect()->to('admin/menus')->with('message', 'Menu updated.');
    }

    public function delete(int $id)
    {
        $model = new MenuModel();
        if (! $model->find($id)) {
            return redirect()->to('admin/menus')->with('error', 'Menu not found.');
        }
        // detach children to top level, then delete
        $model->where('parent_id', $id)->set('parent_id', null)->update();
        $model->delete($id);

        return redirect()->to('admin/menus')->with('message', 'Menu deleted.');
    }

    private function parentIdFromPost(): ?int
    {
        $parent = $this->request->getPost('parent_id');

        return $parent ? (int) $parent : null;
    }

    /**
     * Validate that assigning $parentId to $menuId keeps the tree acyclic and
     * within MAX_DEPTH levels. Returns an error message, or null if OK.
     */
    private function parentDepthError(MenuModel $model, ?int $menuId, ?int $parentId): ?string
    {
        if ($parentId === null) {
            return null; // top level, always fine
        }
        if ($menuId !== null && $parentId === $menuId) {
            return 'A menu cannot be its own parent.';
        }

        // walk up from the chosen parent to the root, counting depth and
        // detecting whether we loop back into $menuId (a cycle).
        $depth   = 1; // the chosen parent itself is at least level 1
        $current = $model->find($parentId);
        $guard   = 0;
        while ($current) {
            if ($menuId !== null && (int) $current['id'] === $menuId) {
                return 'A menu cannot be moved under one of its own descendants.';
            }
            $depth++;
            if ($current['parent_id'] === null) {
                break;
            }
            $current = $model->find((int) $current['parent_id']);
            if (++$guard > self::MAX_DEPTH + 2) {
                break; // safety against corrupt data
            }
        }

        // $depth = level the NEW item would occupy. Must not exceed MAX_DEPTH.
        if ($depth >= self::MAX_DEPTH + 1) {
            return 'Maximum menu depth is ' . self::MAX_DEPTH . ' levels.';
        }

        return null;
    }

    private function payload(): array
    {
        return [
            'parent_id'  => $this->parentIdFromPost(),
            'title'      => $this->request->getPost('title'),
            'url'        => $this->request->getPost('url') ?: '#',
            'icon'       => $this->request->getPost('icon') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ];
    }
}
