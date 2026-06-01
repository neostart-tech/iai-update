<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Publication</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .header { background-color: #01b4d5; padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 30px 20px; color: #334155; line-height: 1.6; }
        .image-container { text-align: center; margin-bottom: 20px; }
        .image-container img { max-width: 100%; border-radius: 8px; }
        .title { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 15px; }
        .excerpt { color: #64748b; font-size: 15px; margin-bottom: 25px; }
        .button-container { text-align: center; margin-top: 30px; margin-bottom: 10px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #01b4d5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; text-align: center; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; }
        .badge { display: inline-block; padding: 4px 12px; background-color: #e0f2fe; color: #0284c7; border-radius: 9999px; font-size: 12px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ESCEN</h1>
        </div>
        
        <div class="content">
            @php
                $isBlog = $type === 'blog';
                $title = $isBlog ? $model->title : $model->nom;
                $excerpt = $isBlog ? mb_strimwidth(html_entity_decode(strip_tags($model->content)), 0, 150, '...') : mb_strimwidth(html_entity_decode(strip_tags($model->details)), 0, 150, '...');
                $slug = $isBlog ? $model->slug : ('evt_' . $model->id);
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                $link = rtrim($frontendUrl, '/') . '/blogs/' . $slug;
                $imageUrl = $model->image ? asset(\Illuminate\Support\Facades\Storage::url($model->image)) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085';
            @endphp

            <span class="badge">
                {{ $isBlog ? 'Nouvel Article' : 'Nouvel Événement' }}
            </span>

            <h2 class="title">{{ $title }}</h2>
            
            <div class="image-container">
                <img src="{{ $imageUrl }}" alt="{{ $title }}">
            </div>

            <p class="excerpt">
                {{ $excerpt }}
            </p>

            <div class="button-container">
                <a href="{{ $link }}" class="button">Découvrir maintenant</a>
            </div>
        </div>

        <div class="footer">
            <p>Vous recevez cet email car vous êtes inscrit à notre newsletter.</p>
            <p>&copy; {{ date('Y') }} ESCEN. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
