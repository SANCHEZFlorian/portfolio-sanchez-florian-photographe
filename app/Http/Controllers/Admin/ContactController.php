<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->get();

        return inertia('Admin/Contact/Index', [
            'contacts' => $contacts,
            'unreadCount' => Contact::where('is_read', false)->count(),
        ]);
    }

    public function toggleRead(Contact $contact)
    {
        $contact->update(['is_read' => !$contact->is_read]);

        return back();
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Message supprimé.');
    }
}
