<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ImportPaymentBatch;
use App\Actions\Payments\SubmitPaymentBatch;
use App\Models\PaymentBatch;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadBatch extends Component
{
    use WithFileUploads;

    public $file;
    public ?PaymentBatch $batch = null;
    public bool $showPreview = false;

    public function validateBatch(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->batch = app(ImportPaymentBatch::class)->handle(
            $this->file,
            auth()->user(),
        );

        $this->showPreview = true;
        $this->reset('file');

        session()->flash('success', "Batch {$this->batch->batch_id} created with {$this->batch->record_count} records.");
    }

    public function submit(): void
    {
        if (! $this->batch) {
            return;
        }

        $this->authorize('submit', $this->batch);

        app(SubmitPaymentBatch::class)->handle($this->batch, auth()->user());

        $this->batch->refresh();

        session()->flash('success', "Batch {$this->batch->batch_id} submitted. Status: {$this->batch->status->label()}");
    }

    public function downloadTemplate()
    {
        $headers = ['Employee Name', 'MPesa Phone Number', 'Payment Amount', 'Employee Code', 'Narration'];
        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment_template.csv"',
        ]);
    }

    public function render()
    {
        $validItems = null;
        $invalidItems = null;

        if ($this->batch) {
            $validItems = $this->batch->validItems()->get();
            $invalidItems = $this->batch->invalidItems()->get();
        }

        return view('livewire.payments.upload-batch', [
            'validItems' => $validItems,
            'invalidItems' => $invalidItems,
        ])->layout('components.layouts.app');
    }
}
