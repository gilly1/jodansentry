<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ImportPaymentBatch;
use App\Actions\Payments\SubmitPaymentBatch;
use App\Models\PaymentBatch;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadBatch extends Component
{
    use WithFileUploads;

    public $file;
    public ?PaymentBatch $batch = null;
    public bool $uploading = false;

    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->upload();
    }

    public function upload()
    {
        $this->uploading = true;

        try {
            $action = app(ImportPaymentBatch::class);
            $this->batch = $action->execute($this->file, auth()->id());
            $this->batch->load(['items']);
        } catch (\Exception $e) {
            $this->addError('file', 'Failed to process file: ' . $e->getMessage());
        } finally {
            $this->uploading = false;
        }
    }

    public function submit()
    {
        if (!$this->batch || $this->batch->valid_records === 0) {
            return;
        }

        app(SubmitPaymentBatch::class)->execute($this->batch);
        session()->flash('success', 'Batch submitted successfully.');
        return redirect()->route('payments.batches');
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo "employee_name,employee_code,phone_number,amount,narration\n";
            echo "John Doe,EMP001,0712345678,50000,Salary June 2026\n";
            echo "Jane Smith,EMP002,0798765432,45000,Salary June 2026\n";
        }, 'payment_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        return view('livewire.payments.upload-batch');
    }
}
