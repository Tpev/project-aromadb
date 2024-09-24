<x-app-layout>
    <x-slot name="header">
@section('title', 'Articles du Blog')
    </x-slot>

    <!-- Ensure Font Awesome icons are loaded -->
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <div class="container mt-5">
        <h1 class="page-title">Articles du Blog</h1>

<!-- Description Section for Blog Page -->
<div class="description-box">
    <p class="description-text">
        Bienvenue sur notre blog dédié à l’aromathérapie, aux huiles essentielles, et au bien-être. Découvrez une vaste sélection d’articles détaillés couvrant les meilleures pratiques, des guides d’utilisation, et des conseils pour intégrer l’aromathérapie dans votre vie quotidienne. Que vous cherchiez à améliorer votre sommeil, renforcer votre immunité, ou purifier votre environnement, nos articles vous offrent des solutions naturelles, basées sur des expertises fiables et des études approfondies. Ce blog est un espace d’apprentissage et d’inspiration pour les passionnés du bien-être naturel et les thérapeutes. Explorez nos articles pour enrichir vos connaissances et appliquer les bienfaits des huiles essentielles au quotidien.
    </p>
</div>
        <!-- Search Bar -->
        <div class="mb-4 text-end">
            <input type="text" id="search" class="form-control" placeholder="Recherche par titre..." onkeyup="filterPosts()" style="border-color: #854f38;">
        </div>

        <!-- Blog Post Grid -->
        <div class="grid-container" id="blogGrid">
            @foreach($posts as $post)
                <div class="blog-card" onclick="animateAndRedirect(this, '{{ route('blog.show', $post->slug) }}');">
                    <h2>{{ $post->Title }}</h2>
                    <p>{{ Str::limit($post->MetaDescription, 100) }}</p>
                    <div class="tags">
                        @foreach(explode(',', $post->Tags) as $tag)
                            <span class="tag">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
	    .description-box {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 20px 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
    }

    .description-box:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .description-text {
        font-size: 1.2rem;
        line-height: 1.7;
        color: #333;
        text-align: justify;
    }

    /* Animation */
    .description-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 300%;
        height: 100%;
        background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        transition: all 0.3s ease-in-out;
    }

    .description-box:hover::before {
        left: 100%;
    }
        .container {
            max-width: 1200px;
            text-align: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        #search {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #854f38;
            margin-right: 15px;
        }

        .text-end {
            padding-right: 15px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .blog-card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .blog-card:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .blog-card h2 {
            font-size: 1.5rem;
            color: #647a0b;
            margin-bottom: 10px;
        }

        .blog-card p {
            font-size: 1rem;
            color: #555555;
            margin-bottom: 15px;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .tag {
            background-color: #647a0b;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .tag:hover {
            background-color: #854f38;
        }
    </style>

    <!-- JavaScript for row click animation and filtering -->
    <script>
        function animateAndRedirect(element, url) {
            element.classList.add('active');
            setTimeout(function() {
                window.location.href = url;
            }, 500);
        }

        function filterPosts() {
            let input = document.getElementById('search');
            let filter = input.value.toLowerCase();
            let grid = document.getElementById('blogGrid');
            let cards = grid.getElementsByClassName('blog-card');

            for (let i = 0; i < cards.length; i++) {
                let h2 = cards[i].getElementsByTagName('h2')[0];
                if (h2) {
                    let txtValue = h2.textContent || h2.innerText;
                    cards[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }
    </script>
</x-app-layout>
