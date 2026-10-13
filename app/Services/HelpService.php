<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\HelpTicket;

class HelpService
{
    // ─── Tickets ──────────────────────────────────────────────────
    public function listTickets(int $userId, int $perPage = 15)
    {
        return HelpTicket::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function createTicket(array $data, int $userId): HelpTicket
    {
        return HelpTicket::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'open',
        ]);
    }

    public function updateTicket(int $id, array $data): HelpTicket
    {
        $ticket = HelpTicket::findOrFail($id);
        $ticket->update($data);

        return $ticket;
    }

    // ─── FAQs ─────────────────────────────────────────────────────
    public function listFaqs(?string $category = null)
    {
        $query = Faq::where('is_active', true)->orderBy('sort_order');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }
}
