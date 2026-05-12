# Olithea Branding Guidelines

Last updated: 2026-05-12

## Brand Identity

Olithea is the new public brand name for AromaMade.

| Old value | New value |
| --- | --- |
| AromaMade | Olithea |
| aromamade.com | olithea.fr |
| contact@aromamade.com | contact@olithea.fr |

Avoid showing "AromaMade" in new user-facing surfaces unless a deliberate transition note is required.

## Logo Assets

Current source files:

| File | Dimensions | Use |
| --- | ---: | --- |
| `D:\Download\logo.png` | 1536 x 1024 | Full logo option |
| `D:\Download\logo1.png` | 1536 x 1024 | Full logo option |
| `D:\Download\logo2.png` | 1536 x 1024 | Full logo option |
| `D:\Download\logo_alone.png` | 1024 x 1024 | Symbol-only mark, favicon/app icon candidate |

Repo-ready assets:

| File | Use |
| --- | --- |
| `public/images/brand/olithea-logo-horizontal-green-cropped.png` | Primary app/header logo |
| `public/images/brand/olithea-logo-horizontal-accent-cropped.png` | Alternate warmer horizontal logo |
| `public/images/brand/olithea-logo-stacked-cropped.png` | Stacked brand moments and auth/marketing pages |
| `public/images/brand/olithea-mark-cropped.png` | Symbol-only mark and favicon/app icon candidate |

Implementation notes:

- Prefer a clean transparent SVG export for production if available.
- Use the full horizontal logo where there is enough width: header, auth pages, email headers, PDF headers.
- Use the symbol-only mark for favicon, compact mobile UI, square social/avatar contexts, and app icons.
- Avoid placing the current low-contrast logo treatment on busy photographic backgrounds.
- Keep clear space around the logo at least equal to the symbol circle stroke width multiplied by 4.

## Typography

Primary display typeface: **Cormorant Garamond**

Use for:

- Logo-adjacent brand moments
- Marketing headings
- Public page hero headings
- Editorial section headings
- PDF cover/title moments where space allows

Secondary/UI typeface: **Montserrat**

Use for:

- Body copy
- Buttons
- Forms
- Dashboard labels
- Navigation
- Tables
- Email body text
- Small UI text

Notes:

- Montserrat is preferred over Avenir Next for web implementation because it is easier to load consistently and license safely.
- Avenir Next may be used as a local/system fallback or for print material where licensing is already covered.
- Do not use Cormorant Garamond for dense dashboard body text, small form labels, or tables.

Suggested CSS font stacks:

```css
--font-display: "Cormorant Garamond", Georgia, serif;
--font-sans: "Montserrat", "Avenir Next", Avenir, system-ui, sans-serif;
```

## Color Palette

| Role | Hex | Usage |
| --- | --- | --- |
| Primary green | `#A7B88A` | Brand fill, soft panels, selected states, decorative accents |
| Secondary brown | `#6B4A3A` | Primary readable text, headings, important UI, outlines |
| Background beige | `#F6F2EB` | Main page background |
| Warm auxiliary | `#EDE7DB` | Section bands, cards, subtle panels |
| Cool auxiliary | `#B8CDBD` | Supporting highlights, status surfaces, calm contrast blocks |
| Accent apricot | `#E9B07A` | Calls to attention, small accents, badges, secondary highlights |

## Accessibility And Contrast

The palette is coherent and suitable for a warm wellness/professional identity, but the green is intentionally soft and should not be used as small text on light backgrounds.

Checked contrast ratios:

| Pair | Ratio | Guidance |
| --- | ---: | --- |
| Brown `#6B4A3A` on beige `#F6F2EB` | 7.06 | Passes normal text |
| Brown `#6B4A3A` on warm aux `#EDE7DB` | 6.40 | Passes normal text |
| Brown `#6B4A3A` on cool aux `#B8CDBD` | 4.69 | Passes normal text |
| Brown `#6B4A3A` on green `#A7B88A` | 3.70 | Use for large text/buttons only |
| Brown `#6B4A3A` on apricot `#E9B07A` | 4.11 | Use carefully; better for larger labels |
| Green `#A7B88A` on beige `#F6F2EB` | 1.91 | Do not use for readable text |
| White on green `#A7B88A` | 2.13 | Do not use for readable text |
| Black/dark text on green `#A7B88A` | 9.87 | Safe |
| Black/dark text on apricot `#E9B07A` | 10.96 | Safe |

Practical rules:

- Use brown `#6B4A3A` as the default text color on beige/warm backgrounds.
- Use dark text, not white text, on green and apricot buttons.
- Use primary green as a background, border, icon, or brand accent rather than body text.
- For primary buttons, prefer green background with brown text, or brown background with white text.
- Keep beige as the main background but avoid making every surface beige; use white or warm auxiliary panels to maintain structure.

## Recommended UI Roles

```css
--brand-primary: #A7B88A;
--brand-secondary: #6B4A3A;
--brand-background: #F6F2EB;
--brand-surface-warm: #EDE7DB;
--brand-surface-cool: #B8CDBD;
--brand-accent: #E9B07A;
--brand-text: #6B4A3A;
--brand-text-strong: #3F2B22;
--brand-border: #D8CFBF;
```

Suggested interaction mapping:

- Primary action: green background, strong brown text.
- Destructive action: keep existing red/error conventions, do not force the brand palette.
- Link: brown text with underline or stronger brown on hover.
- Focus ring: apricot or cool auxiliary with enough visible thickness.
- Success: use green family, but with dark text.
- Warning/highlight: apricot family, with dark text.

## Brand Direction

The current direction reads as:

- Natural
- Calm
- Premium wellness
- Softer and more editorial than pure SaaS
- Appropriate for therapeutic, client-facing, and practitioner-facing surfaces

Implementation should preserve product clarity. Dashboards, forms, calendars, invoices, and admin tools should stay readable, structured, and efficient rather than becoming overly decorative.

## First Rebrand Targets

Recommended order:

1. Global brand strings: app name, domains, contact email.
2. Logo and favicon assets.
3. Base layout: header, footer, auth screens.
4. Core CSS/Tailwind tokens.
5. Public marketing pages.
6. Emails and PDF templates.
7. Dashboard accents and form controls.
8. Legal pages and SEO metadata.

## Acceptance Checklist

- No unintended "AromaMade", `aromamade.com`, or `contact@aromamade.com` remains visible.
- Browser title, favicon, header, footer, auth pages, emails, and PDFs use Olithea.
- Body text remains readable on beige, green, apricot, and auxiliary surfaces.
- Logo is crisp at header, mobile, email, PDF, and favicon sizes.
- Staging remains password protected and noindexed during the rebrand.
