<?php

namespace App\Http\Livewire\Products;

use App\Exports\ProductExport;
use App\Http\Livewire\WithSorting;
use App\Imports\ProductImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Notifications\ProductTelegram;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithSorting;
    use LivewireAlert;
    use WithPagination;
    use WithFileUploads;

    public $product;

    public $listeners = [
        'confirmDelete', 'delete', 'showModal', 'editModal',
        'refreshIndex', 'exportExcel', 'exportPdf',
        'importModal', 'sendTelegram',
    ];

    public int $perPage;

   /** @var boolean */
   public $showModal = false;
    
   /** @var boolean */
   public $importModal = false;
   
   /** @var boolean */
   public $editModal = false;

    public $refreshIndex;

    public $sendTelegram;

    public array $orderable;

    public $image;

    public string $search = '';

    public array $selected = [];

    public array $paginationOptions;

    public array $listsForFields = [];

    protected $queryString = [
        'search' => [
            'except' => '',
        ],
        'sortBy' => [
            'except' => 'id',
        ],
        'sortDirection' => [
            'except' => 'desc',
        ],
    ];

    public function getSelectedCountProperty()
    {
        return count($this->selected);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetSelected()
    {
        $this->selected = [];
    }

    public function refreshIndex()
    {
        $this->resetPage();
    }

    public array $rules = [
        'product.name' => ['required', 'string', 'max:255'],
        'product.code' => ['required', 'string', 'max:255'],
        'product.barcode_symbology' => ['required', 'string', 'max:255'],
        'product.unit' => ['required', 'string', 'max:255'],
        'product.quantity' => ['required', 'integer', 'min:1'],
        'product.cost' => ['required', 'numeric', 'max:2147483647'],
        'product.price' => ['required', 'numeric', 'max:2147483647'],
        'product.stock_alert' => ['required', 'integer', 'min:0'],
        'product.order_tax' => ['nullable', 'integer', 'min:0', 'max:100'],
        'product.tax_type' => ['nullable', 'integer'],
        'product.note' => ['nullable', 'string', 'max:1000'],
        'product.category_id' => ['required', 'integer'],
        'product.brand_id' => ['nullable', 'integer'],
    ];

    public function mount()
    {
        $this->sortBy = 'id';
        $this->sortDirection = 'desc';
        $this->perPage = 100;
        $this->paginationOptions = config('project.pagination.options');
        $this->orderable = (new Product)->orderable;
        $this->initListsForFields();
    }

    public function deleteSelected()
    {
        abort_if(Gate::denies('product_delete'), 403);

        Product::whereIn('id', $this->selected)->delete();

        $this->resetSelected();
    }

    public function delete(Product $product)
    {
        abort_if(Gate::denies('product_delete'), 403);

        $product->delete();
    }

    public function render()
    {
        abort_if(Gate::denies('product_access'), 403);

        $query = Product::query()
            ->with([
                'category' => fn ($query) => $query->select('id', 'name'),
                'brand' => fn ($query) => $query->select('id', 'name'),
            ])
            ->select('products.*')
            ->advancedFilter([
                's' => $this->search ?: null,
                'order_column' => $this->sortBy,
                'order_direction' => $this->sortDirection,
            ]);

        $products = $query->paginate($this->perPage);

        return view('livewire.products.index', compact('products'));
    }

    public function showModal(Product $product)
    {
        abort_if(Gate::denies('product_access'), 403);

        $this->product = Product::find($product->id);

        $this->showModal = true;
    }

    public function sendTelegram(Product $product)
    {
        $this->product = Product::find($product->id);

        $this->product->notify(new ProductTelegram);

    }

    public function editModal(Product $product)
    {
        abort_if(Gate::denies('product_update'), 403);

        $this->resetErrorBag();

        $this->resetValidation();

        $this->product = Product::find($product->id);

        $this->editModal = true;
    }

    public function update()
    {

        $this->validate();

        if ($this->image) {
            $imageName = Str::slug($this->product->name).'-'.date('Y-m-d H:i:s').'.'.$this->image->extension();
            $this->image->storeAs('products', $imageName);
            $this->product->image = $imageName;
        }

        $this->product->save();

        $this->editModal = false;

        $this->alert('success', __('Product updated successfully.'));
    }

    public function importModal()
    {
        
        abort_if(Gate::denies('product_access'), 403);

        $this->resetErrorBag();

        $this->resetValidation();

        $this->importModal = true;
    }

    public function import()
    {
        
        $this->validate([
            'import_file' => [
                'required',
                'file',
            ],
        ]);

        Product::import(new ProductImport, $this->file('import_file'));

        $this->alert('success', __('Products imported successfully'));

        $this->importModal = false;
    }

    public function exportExcel()
    {
        abort_if(Gate::denies('product_access'), 403);

        return (new ProductExport)->download('products.xlsx');
    }

    public function exportPdf()
    {

        return (new ProductExport)->download('products.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }

    protected function initListsForFields(): void
    {
        $this->listsForFields['categories'] = Category::pluck('name', 'id')->toArray();
        $this->listsForFields['brands'] = Brand::pluck('name', 'id')->toArray();
        $this->listsForFields['warehouses'] = Warehouse::pluck('name', 'id')->toArray();
    }
}
