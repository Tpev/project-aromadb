{{-- resources/views/products/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl" style="color: #647a0b;">
            {{ __('Détails de la Prestation') }}
        </h2>
    </x-slot>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <div class="container mt-5">
        <div class="details-container mx-auto p-4">

            {{-- Image --}}
            @if($product->image)
                <div class="product-image-container mb-4">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-image">
                </div>
            @endif

            <h1 class="details-title flex items-center justify-center gap-3">
                {{ $product->name }}

                {{-- Badge: fiche d’émargement requise (only if true) --}}
                @if($product->requires_emargement)
                    <span class="badge badge-green" title="Fiche d’émargement requise">
                        <i class="fas fa-file-signature"></i> {{ __('Émargement requis') }}
                    </span>
                @endif
            </h1>

            <div class="product-info mt-4">

                <!-- Description -->
                @if($product->description)
                    <div class="product-box">
                        <i class="fas fa-align-left icon"></i>
                        <div class="product-details">
                            <p class="product-label">{{ __('Description') }}</p>
                            <p class="product-value">{!! nl2br(e($product->description)) !!}</p>
                        </div>
                    </div>
                @endif

                <!-- Prix -->
                <div class="product-box">
                    <i class="fas fa-euro-sign icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Prix') }}</p>
                        <p class="product-value">{{ number_format($product->price, 2, ',', ' ') }} €</p>
                    </div>
                </div>

                <!-- TVA -->
                <div class="product-box">
                    <i class="fas fa-percentage icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('TVA') }}</p>
                        <p class="product-value">{{ number_format($product->tax_rate, 2, ',', ' ') }} %</p>
                    </div>
                </div>

                <!-- Durée -->
                <div class="product-box">
                    <i class="fas fa-clock icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Durée') }}</p>
                        <p class="product-value">{{ $product->duration ? $product->duration . ' min' : 'N/A' }}</p>
                    </div>
                </div>

                <!-- Mode -->
                <div class="product-box">
                    <i class="fas fa-map-marker-alt icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Mode') }}</p>
                        <p class="product-value">
                            @if($product->visio) {{ __('Visio') }}
                            @elseif($product->adomicile) {{ __('À domicile') }}
                            @elseif($product->en_entreprise) {{ __('En entreprise') }}
                            @elseif($product->dans_le_cabinet) {{ __('Dans le cabinet') }}
                            @else N/A @endif
                        </p>
                    </div>
                </div>

                <!-- Bookable online -->
                <div class="product-box">
                    <i class="fas fa-globe icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Réservable en ligne') }}</p>
                        <p class="product-value">{{ $product->can_be_booked_online ? __('Oui') : __('Non') }}</p>
                    </div>
                </div>

                <!-- Paiement requis -->
                <div class="product-box">
                    <i class="fas fa-credit-card icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Paiement requis') }}</p>
                        <p class="product-value">{{ $product->collect_payment ? __('Oui') : __('Non') }}</p>
                    </div>
                </div>

                <!-- Visible in portal -->
                <div class="product-box">
                    <i class="fas fa-eye icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Visible sur le portail') }}</p>
                        <p class="product-value">{{ $product->visible_in_portal ? __('Oui') : __('Non') }}</p>
                    </div>
                </div>

                <!-- Prix visible in portal -->
                <div class="product-box">
                    <i class="fas fa-tags icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Prix visible sur le portail') }}</p>
                        <p class="product-value">{{ $product->price_visible_in_portal ? __('Oui') : __('Non') }}</p>
                    </div>
                </div>

                <!-- Display order -->
                <div class="product-box">
                    <i class="fas fa-sort-numeric-down icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Ordre d’affichage') }}</p>
                        <p class="product-value">{{ $product->display_order ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Max per day -->
                <div class="product-box">
                    <i class="fas fa-calendar-day icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Nombre maximum de séances par jour') }}</p>
                        <p class="product-value">{{ $product->max_per_day ?: 'N/A' }}</p>
                    </div>
                </div>

                <!-- Brochure -->
                @if($product->brochure)
                    <div class="product-box">
                        <i class="fas fa-file-pdf icon"></i>
                        <div class="product-details">
                            <p class="product-label">{{ __('Brochure') }}</p>
                            <p class="product-value">
                                <a href="{{ asset('storage/' . $product->brochure) }}" target="_blank" class="link">
                                    {{ __('Télécharger la brochure') }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Lien réservation directe (privé / partenaire) --}}
            @if(auth()->check() && auth()->id() === $product->user_id)
                <div class="product-box">
                    <i class="fas fa-link icon"></i>
                    <div class="product-details">
                        <p class="product-label">{{ __('Lien réservation directe') }}</p>

                        @if(!empty($directBookingLink))
                            <p class="product-value" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="text"
                                       class="form-control"
                                       id="directBookingLinkInput"
                                       value="{{ url('/b/' . $directBookingLink->token) }}"
                                       readonly
                                       style="max-width:520px;">
                                <button type="button" class="btn-secondary" onclick="copyDirectBookingLink()">
                                    {{ __('Copier') }}
                                </button>
                            </p>
                            <p class="product-value" style="margin-top:6px;">
                                <span class="text-gray-500">{{ __('Lien privé à partager à un partenaire.') }}</span>
                            </p>
                        @else
                            <p class="product-value">
                                <span class="text-gray-500">{{ __('Désactivé. Activez-le dans “Modifier” → Options avancées → Liens réservation directe.') }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="action-buttons mt-4">
                <a href="{{ route('products.edit', $product->id) }}" class="btn-primary">
                    <i class="fas fa-edit"></i> {{ __('Modifier') }}
                </a>

                {{-- ✅ Dupliquer (owner-only) --}}
                @if(auth()->check() && auth()->id() === $product->user_id)
                    <a href="{{ route('products.duplicate', $product->id) }}" class="btn-secondary">
                        <i class="fas fa-clone"></i> {{ __('Dupliquer') }}
                    </a>
                @endif

                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-secondary"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette prestation ?')">
                        <i class="fas fa-trash-alt"></i> {{ __('Supprimer') }}
                    </button>
                </form>

                <a href="{{ route('products.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Retour') }}
                </a>
            </div>
        </div>
    </div>

    <style>
        .container { max-width: 900px; }

        .details-container {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 0 auto;
        }

        .details-title {
            font-size: 2rem;
            font-weight: bold;
            color: #647a0b;
            margin-bottom: 20px;
            text-align: center;
        }

        .product-image-container { text-align: center; }
        .product-image { max-width: 100%; border-radius: 10px; }

        .product-info { display: grid; gap: 15px; }

        .product-box {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .icon { color: #647a0b; margin-top: 4px; }

        .product-label { font-weight: bold; color: #647a0b; margin: 0; }
        .product-value { margin: 0; color: #333; }

        .action-buttons { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; }

        .btn-primary {
            background-color: #647a0b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary {
            background-color: #ccc;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .badge {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 800;
        }

        .badge-green {
            background: rgba(100, 122, 11, 0.12);
            color: #647a0b;
        }
    </style>

    <script>
        function copyDirectBookingLink() {
            const el = document.getElementById('directBookingLinkInput');
            if (!el) return;

            el.select();
            el.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
            } catch (e) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(el.value);
                }
            }
        }
    </script>
</x-app-layout>
