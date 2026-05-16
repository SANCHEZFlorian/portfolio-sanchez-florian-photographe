<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; color: #1a1a2e; line-height: 1.6; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 8px; }
        .header { border-bottom: 2px solid #293ecc; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #293ecc; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #666; font-size: 0.9rem; }
        .value { margin-top: 5px; background: #f9f9f9; padding: 10px; border-radius: 4px; }
        .footer { margin-top: 30px; font-size: 0.8rem; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouveau message de contact</h2>
        </div>
        
        <div class="field">
            <div class="label">Nom</div>
            <div class="value">{{ $contact->name }}</div>
        </div>
        
        <div class="field">
            <div class="label">Email</div>
            <div class="value">{{ $contact->email }}</div>
        </div>
        
        @if($contact->subject)
        <div class="field">
            <div class="label">Sujet</div>
            <div class="value">{{ $contact->subject }}</div>
        </div>
        @endif
        
        <div class="field">
            <div class="label">Message</div>
            <div class="value">{!! nl2br(e($contact->message)) !!}</div>
        </div>
        
        <div class="footer">
            Ce message a été envoyé via le formulaire de contact de {{ config('app.name') }}.
        </div>
    </div>
</body>
</html>
