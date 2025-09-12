<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Quote;
use Illuminate\Support\Carbon;

class QuoteRepository extends Repository
{
    public function __construct(Quote $model)
    {
        parent::__construct($model);
    }

    /**
     * Find all quotes for a specific customer.
     *
     * @param int $customerId
     * @return \Illuminate\Support\Collection
     */
    public function findByCustomerId(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }

    /**
     * Find a quote with its related details (customer, address, cart, job).
     *
     * @param int $id
     * @return Quote|null
     */
    public function findWithDetails(int $id): ?Quote
    {
        return $this->model
            ->with(['customer', 'customerAddress', 'cart', 'job'])
            ->find($id);
    }

    /**
     * Get all quotes with details.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithDetails()
    {
        return $this->model->with(['customer', 'customerAddress', 'cart', 'job'])->get();
    }

    /**
     * Find all active quotes (not expired).
     *
     * @return \Illuminate\Support\Collection
     */
    public function findActive()
    {
        return $this->model
            ->where('valid_until', '>=', Carbon::now())
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Mark a quote as accepted.
     *
     * @param int $quoteId
     * @return bool
     */
    public function acceptQuote(int $quoteId): bool
    {
        $quote = $this->findById($quoteId);

        if (!$quote) {
            return false;
        }

        return $quote->update(['status' => 'accepted']);
    }

    /**
     * Mark a quote as rejected.
     *
     * @param int $quoteId
     * @return bool
     */
    public function rejectQuote(int $quoteId): bool
    {
        $quote = $this->findById($quoteId);

        if (!$quote) {
            return false;
        }

        return $quote->update(['status' => 'rejected']);
    }
}
