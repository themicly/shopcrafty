<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Catalog\Services\ProductCsv;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;

    /** @var array{created:int, updated:int, errors:array<int, string>}|null */
    public ?array $result = null;

    public function import(ProductCsv $csv): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $this->result = $csv->import(file_get_contents($this->file->getRealPath()));
        $this->reset('file');

        $this->dispatch('products-imported'); // refresh the product list behind the drawer
        $this->dispatch('toast',
            message: "Imported: {$this->result['created']} new, {$this->result['updated']} updated",
            type: 'success',
        );
    }

    public function render()
    {
        return View::make('catalog::livewire.product-import', ['columns' => ProductCsv::COLUMNS]);
    }
}
