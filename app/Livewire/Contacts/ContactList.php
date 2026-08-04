<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Livewire\Component;
use Livewire\WithPagination;

class ContactList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $contacts = Contact::query()
            ->when($this->search, fn($q) => $q->where('mpesa_name', 'like', "%{$this->search}%")
                ->orWhere('phone_number', 'like', "%{$this->search}%"))
            ->orderBy('mpesa_name')
            ->paginate(20);

        return view('livewire.contacts.contact-list', compact('contacts'));
    }
}
