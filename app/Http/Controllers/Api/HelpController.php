<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Http\Resources\FaqResource;
use App\Http\Resources\HelpTicketResource;
use App\Services\HelpService;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function __construct(
        private readonly HelpService $helpService
    ) {}

    // ─── Tickets ──────────────────────────────────────────────────
    public function tickets(Request $request)
    {
        $tickets = $this->helpService->listTickets(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(HelpTicketResource::collection($tickets), $tickets);
    }

    public function storeTicket(StoreTicketRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new HelpTicketResource(
                $this->helpService->createTicket($request->sanitized(), $request->user()->id)
            ),
            'message' => 'Ticket created',
        ], 201);
    }

    // ─── FAQs ─────────────────────────────────────────────────────
    public function faqs(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => FaqResource::collection(
                $this->helpService->listFaqs($request->get('category'))
            ),
        ]);
    }
}
