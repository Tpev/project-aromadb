<x-app-layout>
    <!-- Meta Description -->
@section('meta_description')
    {{ $post->MetaDescription ?? 'Default meta description for this blog post.' }}
@endsection
@section('title', 'Article ' .  $post->Title )

    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ $post->Title }}
        </h2>
    </x-slot>

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">
            <h1 class="details-title">{{ $post->Title }}</h1>

            <!-- Blog Post Content -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-tags" style="color: #647a0b;"></i> Tags</label>
                        <p class="details-value">{{ $post->Tags }}</p>
                    </div>
								@php

				$imagePath = 'images/' . $post->slug . '.webp';
			@endphp

			<div class="col-md-6 text-center">
				@if (File::exists(public_path($imagePath)))
					<img src="{{ asset($imagePath) }}" alt="{{ $post->Title }}" class="img-fluid huile-image">
				@else
					<img src="{{ asset('images/default.webp') }}" alt="{{ $post->Title }}" class="img-fluid huile-image">
				@endif
			</div>
                    <div class="details-box">
                        <label class="details-label"><i class="fas fa-align-left" style="color: #647a0b;"></i> Contenu</label>
                        <p class="details-value">{!! $post->Contents !!}</p>
                    </div>
                </div>
            </div>

            <!-- Related Posts Section -->
            @if($post->RelatedPostsREF)
                <div class="details-box mt-4">
                    <label class="details-label"><i class="fas fa-book-open" style="color: #647a0b;"></i> Articles Connexes</label>
                    @php
                        $relatedPosts = App\Models\BlogPost::whereIn('REF', explode(',', $post->RelatedPostsREF))->get();
                    @endphp
                    <ul class="details-list">
                        @foreach($relatedPosts as $related)
                            <li class="mb-2">
                                <a href="{{ route('blog.show', $related->slug) }}" class="recette-link">
                                     {{ $related->Title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('blog.index') }}" class="btn-primary mt-4">Retour à la liste des articles</a>

            <!-- Warning Box -->
            <div class="warning-box mt-5 p-4">
                <p class="warning-text">
                    <strong>Attention :</strong> Les informations fournies dans ce blog sont uniquement à titre informatif. Consultez un professionnel avant toute décision basée sur ces informations.
                </p>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .recette-link {
            text-decoration: none;
            color: #854f38;
        }

        .recette-link:hover {
            text-decoration: underline;
            color: #647a0b;
        }

        .container {
            max-width: 1200px;
        }

        .details-container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 10px;
            text-align: center;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-label {
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 10px;
            display: block;
            font-size: 1.1rem;
        }

        .details-value {
            color: #333333;
            font-size: 1rem;
        }

        .details-list {
            list-style-type: disc;
            margin: 0;
            padding-left: 20px;
        }

        .details-list li {
            margin-bottom: 5px;
            font-size: 1rem;
            color: #333333;
        }

        .btn-primary {
            background-color: #647a0b;
            border-color: #647a0b;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #854f38;
            border-color: #854f38;
        }

        /* Warning Box */
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .warning-text {
            color: #856404;
            font-size: 1rem;
            font-weight: 500;
        }
				.huile-image {
    max-width: 50%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin: 0 auto;
}

@media (max-width: 768px) {
    .huile-image {
        max-width: 80%;
    }
}
    </style>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</x-app-layout>
