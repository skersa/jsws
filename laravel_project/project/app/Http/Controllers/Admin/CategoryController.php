<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent')
            ->withCount(['children', 'auctions']);

        if ($q = $request->input('q')) {
            $query->where(fn ($qb) =>
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('slug', 'like', "%{$q}%")
            );
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'passive') {
            $query->where('is_active', false);
        }

        if ($request->input('type') === 'root') {
            $query->whereNull('parent_id');
        } elseif ($request->input('type') === 'sub') {
            $query->whereNotNull('parent_id');
        }

        $categories = $query->ordered()->paginate(20)->withQueryString();

        $stats = [
            'total'   => Category::count(),
            'active'  => Category::where('is_active', true)->count(),
            'passive' => Category::where('is_active', false)->count(),
            'roots'   => Category::whereNull('parent_id')->count(),
            'subs'    => Category::whereNotNull('parent_id')->count(),
        ];

        return Inertia::render('Admin/Categories/Index', [
            'stats'   => $stats,
            'filters' => [
                'q'      => (string) $request->input('q', ''),
                'status' => (string) $request->input('status', ''),
                'type'   => (string) $request->input('type', ''),
            ],
            'categories' => [
                'data' => collect($categories->items())->map(fn ($c) => [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'slug'           => $c->slug,
                    'image_url'      => $c->image_url,
                    'parent_name'    => $c->parent?->name,
                    'auctions_count' => $c->auctions_count,
                    'children_count' => $c->children_count,
                    'sort_order'     => $c->sort_order,
                    'is_active'      => (bool) $c->is_active,
                    'show_url'       => route('admin.categories.show', $c),
                    'edit_url'       => route('admin.categories.edit', $c),
                    'toggle_url'     => route('admin.categories.toggle', $c),
                    'destroy_url'    => route('admin.categories.destroy', $c),
                ])->values(),
                'links'     => $categories->linkCollection()->toArray(),
                'has_pages' => $categories->hasPages(),
                'total'     => $categories->total(),
                'from'      => $categories->firstItem(),
                'to'        => $categories->lastItem(),
            ],
            'create_url' => route('admin.categories.create'),
        ]);
    }


    public function create()
    {
        return Inertia::render('Admin/Categories/Create', [
            'parents'       => $this->parentOptions(),
            'preset_parent' => request('parent') ? (int) request('parent') : null,
            'store_url'     => route('admin.categories.store'),
            'index_url'     => route('admin.categories.index'),
        ]);
    }

    private function parentOptions(array $excludeIds = []): array
    {
        $out = [];
        $walk = function ($nodes, $depth) use (&$walk, &$out, $excludeIds) {
            foreach ($nodes as $node) {
                if (in_array($node->id, $excludeIds, true)) {
                    continue;
                }
                $out[] = ['id' => $node->id, 'label' => str_repeat('— ', $depth) . $node->name];
                if ($node->childrenRecursive && $node->childrenRecursive->count()) {
                    $walk($node->childrenRecursive, $depth + 1);
                }
            }
        };
        $walk(Category::tree(), 0);

        return $out;
    }


    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('category_success', $category->name. ' kategorisi oluşturuldu.');
    }


    public function show(Category $category)
    {
        $category->loadCount(['children', 'auctions'])
                 ->load(['parent', 'children' => fn ($q) => $q->withCount('auctions')->ordered()]);

        return Inertia::render('Admin/Categories/Show', [
            'category' => [
                'id'             => $category->id,
                'name'           => $category->name,
                'slug'           => $category->slug,
                'description'    => $category->description,
                'image_url'      => $category->image_url,
                'is_active'      => (bool) $category->is_active,
                'sort_order'     => $category->sort_order,
                'parent_name'    => $category->parent?->name,
                'auctions_count' => $category->auctions_count,
                'children_count' => $category->children_count,
                'created_year'   => $category->created_at->format('Y'),
                'created_at'     => $category->created_at->format('d M Y, H:i'),
                'updated_at'     => $category->updated_at->format('d M Y, H:i'),
                'edit_url'       => route('admin.categories.edit', $category),
                'index_url'      => route('admin.categories.index'),
                'toggle_url'     => route('admin.categories.toggle', $category),
                'destroy_url'    => route('admin.categories.destroy', $category),
                'create_child_url' => route('admin.categories.create') . '?parent=' . $category->id,
                'children'       => $category->children->map(fn ($c) => [
                    'id'             => $c->id,
                    'name'           => $c->name,
                    'slug'           => $c->slug,
                    'image_url'      => $c->image_url,
                    'auctions_count' => $c->auctions_count,
                    'is_active'      => (bool) $c->is_active,
                    'sort_order'     => $c->sort_order,
                    'show_url'       => route('admin.categories.show', $c),
                    'edit_url'       => route('admin.categories.edit', $c),
                ])->values(),
            ],
        ]);
    }

    public function edit(Category $category)
    {
        $excludeIds = array_merge([$category->id], $category->allChildrenIds());

        return Inertia::render('Admin/Categories/Edit', [
            'parents'  => $this->parentOptions($excludeIds),
            'category' => [
                'id'            => $category->id,
                'name'          => $category->name,
                'slug'          => $category->slug,
                'parent_id'     => $category->parent_id,
                'description'   => $category->description,
                'sort_order'    => $category->sort_order,
                'is_active'     => (bool) $category->is_active,
                'image_url'     => $category->image_url,
                'has_image'     => (bool) $category->image,
                'updated_human' => $category->updated_at->diffForHumans(),
                'update_url'    => route('admin.categories.update', $category),
                'show_url'      => route('admin.categories.show', $category),
                'index_url'     => route('admin.categories.index'),
            ],
        ]);
    }


    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('category_success', $category->name. ' kategorisi güncellendi.');
    }


    public function destroy(Category $category): \Symfony\Component\HttpFoundation\Response
    {
        foreach ($category->children as $child) {
            if ($child->image) Storage::disk('public')->delete($child->image);
            $child->delete();
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $name = $category->name;
        $category->delete();

        $msg = $name . ' ve alt kategorileri silindi.';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => $msg]);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('category_success', $msg);
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        $msg = $category->is_active  ? $category->name. ' aktif edildi.' : $category->name. ' pasife alındı.';

        return back()->with('category_success', $msg);
    }


    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'items'          => ['required', 'array'],
            'items.*.id'     => ['required', 'integer', 'exists:categories,id'],
            'items.*.order'  => ['required', 'integer'],
        ]);

        foreach ($request->input('items') as $item) {
            Category::where('id', $item['id'])->update(['sort_order' => $item['order']]);
        }

        return response()->json(['message' => 'Sıralama güncellendi.']);
    }
}
