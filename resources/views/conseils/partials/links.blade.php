@if($conseil->display_links)
    <section class="conseil-resource-links" aria-label="Liens utiles" style="margin-top: 24px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb;">
        <h2 style="margin: 0 0 8px; color: #647a0b; font-size: 1.15rem; font-weight: 600;">Liens utiles</h2>
        <p style="margin: 0 0 12px; color: #6b7280; font-size: 0.875rem;">Ces liens s’ouvrent dans un nouvel onglet.</p>
        <ul style="margin: 0; padding: 0; list-style: none;">
            @foreach($conseil->display_links as $link)
                <li style="margin: 8px 0; overflow-wrap: anywhere;">
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" style="color: #854f38; text-decoration: underline;">{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
