<?php
namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class Categories extends Component
{
    public $name, $categoryId, $isEditing = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:100|unique:categories,name',
    ];

    public function render()
    {
        $categories = Category::latest()->get();
        return view('livewire.categories', compact('categories'))->layout('layouts.app');
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            ['name' => $this->name]
        );

        $this->showModal = false;
        $this->resetInput();
    }

    public function delete($id)
    {
        Category::find($id)->delete();
    }

    private function resetInput()
    {
        $this->name = '';
        $this->categoryId = null;
    }
}
