<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return response()->json([
            'success' => true,
            'data' => $this->helpService->listTickets(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->helpService->createTicket($validated, $request->user()->id),
            'message' => 'Ticket created',
        ], 201);
    }

    // ─── FAQs ─────────────────────────────────────────────────────
    public function faqs(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->helpService->listFaqs($request->get('category')),
        ]);
    }
}
