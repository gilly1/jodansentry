<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\SuccessfulTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class ContactDetails extends Component
{
    use WithPagination;

    public Contact $contact;

    public function mount(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function render()
    {
        $transactions = $this->contact->successfulTransactions()
            ->with('paymentBatch')
            ->latest('paid_at')
            ->paginate(20);

        return view('livewire.contacts.contact-details', compact('transactions'));
    }
}
