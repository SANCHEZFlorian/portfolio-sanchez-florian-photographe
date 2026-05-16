<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; color: #1a1a2e; line-height: 1.6; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { color: #293ecc; }
        .content { margin-bottom: 30px; }
        .footer { border-top: 1px solid #eee; padding-top: 20px; font-size: 0.85rem; color: #777; text-align: center; }
        .signature { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Merci pour votre message !</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $contact->name }},</p>
            <p>J'ai bien reçu votre message concernant "{{ $contact->subject ?? 'votre demande' }}".</p>
            <p>Je reviendrai vers vous dans les plus brefs délais.</p>
            
            <p class="signature">
                Florian Sanchez<br>
                Photographe
            </p>
        </div>
        
        <div class="footer">
            Ceci est un accusé de réception automatique.<br>
            <a href="{{ config('app.url') }}" style="color: #293ecc; text-decoration: none;">{{ config('app.name') }}</a>
        </div>
    </div>
</body>
</html>
