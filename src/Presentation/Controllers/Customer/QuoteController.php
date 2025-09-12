<?php
namespace App\Presentation\Controllers\Customer;

use App\Application\Services\QuoteService;
use Exception;

class QuoteController
{
    private QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    /**
     * Create a new quote.
     */
    public function create(array $request)
    {
        try {
            $quote = $this->quoteService->createQuote($request);

            return [
                'success' => true,
                'message' => 'Quote created successfully.',
                'data'    => $quote
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create quote: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Accept a quote → creates a job.
     */
    public function accept(int $quoteId)
    {
        try {
            $job = $this->quoteService->acceptQuote($quoteId);

            return [
                'success' => true,
                'message' => 'Quote accepted. Job created successfully.',
                'data'    => $job
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to accept quote: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject a quote.
     */
    public function reject(int $quoteId)
    {
        try {
            $result = $this->quoteService->rejectQuote($quoteId);

            return [
                'success' => $result,
                'message' => $result ? 'Quote rejected successfully.' : 'Quote not found.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject quote: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get quotes by customer ID.
     */
    public function getByCustomer(int $customerId)
    {
        return [
            'success' => true,
            'data'    => $this->quoteService->getQuotesByCustomer($customerId)
        ];
    }

    /**
     * Get all active quotes.
     */
    public function getActive()
    {
        return [
            'success' => true,
            'data'    => $this->quoteService->getActiveQuotes()
        ];
    }
}
