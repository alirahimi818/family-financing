<?php
namespace App\Livewire;

use App\Models\Tag;
use Livewire\Component;

class Tags extends Component
{
    public $name, $tagId, $isEditing = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:100|unique:tags,name',
    ];

    public function render()
    {
        $tags = Tag::latest()->get();
        return view('livewire.tags', compact('tags'))->layout('layouts.app');
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $tag = Tag::findOrFail($id);
        $this->tagId = $id;
        $this->name = $tag->name;
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        Tag::updateOrCreate(
            ['id' => $this->tagId],
            ['name' => $this->name]
        );

        $this->showModal = false;
        $this->resetInput();
    }

    public function delete($id)
    {
        Tag::find($id)->delete();
    }

    private function resetInput()
    {
        $this->name = '';
        $this->tagId = null;
    }
}
