<footer class="bg-dark-800 text-white py-8">
    <div class="container mx-auto px-4 text-center">
        <div class="container mx-auto flex flex-col gap-6 sm:flex-row sm:justify-between">
            <ul class="space-y-2 text-center sm:text-left">
                <li>
                    <a href="/" class="hover:underline text-lg">Accueil</a>
                </li>
                <li>
                    <a href="/nos-practiciens" class="hover:underline text-lg">Trouvez votre praticien en médecine douce</a>
                </li>
            </ul>

            <ul class="space-y-2 text-center sm:text-right">
                <li>
                    <a href="/privacy-policy" class="hover:underline text-lg">
                        Politique de confidentialité
                    </a>
                </li>
                <li>
                    <a href="/cgu" class="hover:underline text-lg">
                        Conditions Générales d'Utilisation
                    </a>
                </li>
                <li>
                    <a href="/cgv" class="hover:underline text-lg">
                        Conditions Générales de Vente
                    </a>
                </li>
            </ul>
        </div>

        <div class="flex justify-center space-x-4 mb-4 mt-6">
            @if (config('services.social.facebook_url'))
                <a href="{{ config('services.social.facebook_url') }}" class="text-brand-surface-cool hover:text-brand-accent"><i class="fab fa-facebook-f"></i></a>
            @endif

            @if (config('services.social.instagram_url'))
                <a href="{{ config('services.social.instagram_url') }}" class="text-brand-surface-cool hover:text-brand-accent"><i class="fab fa-instagram"></i></a>
            @endif
        </div>

        <p class="text-brand-surface-warm">© {{ date('Y') }} Olithea. Tous droits réservés.</p>
    </div>
</footer>
