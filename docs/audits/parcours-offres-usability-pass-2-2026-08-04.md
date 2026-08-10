# Pages et campagnes - audit produit et utilisabilite, passe 2

Date : 4 aout 2026  
Perimetre : `/dashboard-pro/parcours-offres`  
Contexte : application en production, environ 60 praticiens payants  
Methode : lecture du code, parcours complets sur une base SQLite synthetique, tests fonctionnels, verification Playwright aux formats 390 x 844, 768 x 1024 et 1440 x 1000.

## Verdict court

Le module repond a un besoin reel : aider un praticien a presenter une offre, recueillir une demande, poursuivre la relation et comprendre ce qui amene des rendez-vous. Son principal probleme n'etait pas le manque de fonctions, mais le manque de continuité entre elles.

Avant cette passe, une personne pouvait arriver a un ecran techniquement complet sans savoir quelle action etait indispensable, si ses changements etaient deja publics, ni ce qui se passerait apres une demande. Plusieurs cas produisaient aussi des impasses : choix de l'offre reporte sans moyen de le reprendre, publication affichee comme prete alors qu'une destination manquait, ou image importee sans section active.

Apres cette passe, le coeur du produit est nettement plus autonome : la prochaine action est explicite, les blocages sont prioritaires, une offre peut etre associee plus tard, le brouillon et la version publique sont clairement separes, les messages sont presentes progressivement et les contacts conduisent a des actions concretes.

Evaluation sceptique : **8/10 pour un pilote accompagne legerement, 7/10 pour une ouverture totalement autonome**. Un praticien non technique peut maintenant creer et publier une page simple sans assistance. Les campagnes, segments et automatisations restent puissants et demandent encore une courte mise en contexte lors de la premiere utilisation.

## Diagnostic produit et adequation au marche

### Probleme que le module resout vraiment

Le produit est le plus convaincant lorsqu'il formule la promesse suivante :

> Presentez une offre utile, recueillez les demandes et accompagnez chaque contact jusqu'a la prochaine etape, sans devenir specialiste du marketing.

Cette promesse correspond mieux aux praticiens que le vocabulaire de "tunnel", "automation" ou "lead nurturing". La force d'Olithea est la continuité avec les offres, rendez-vous, evenements, profils clients et donnees deja presentes dans l'application.

### Valeur percue forte

- Une page partageable sans construire un site.
- Un formulaire relie directement aux contacts Olithea.
- Une ressource gratuite delivree sans manipulation manuelle.
- Une suite claire vers un rendez-vous, une inscription ou une offre.
- Des messages de suivi prepares et limites.
- L'origine d'un contact et la prochaine action visibles au meme endroit.
- Des resultats exprimes en demandes et actions, pas uniquement en statistiques marketing.

### Risques de faible adoption

- Le terme interne "parcours" reste abstrait s'il est utilise seul. L'intitule visible **Pages et campagnes** doit rester la porte d'entree.
- Les fonctions avancees peuvent faire croire qu'il faut tout configurer avant de publier.
- Les campagnes et segments demandent encore une premiere experience guidee.
- Les resultats deviennent vraiment utiles seulement apres quelques visites et contacts ; les etats vides doivent donc continuer a proposer une action de partage concrete.
- Le produit ne doit pas chercher a reproduire Systeme.io. Sa differentiation est la simplicite et l'integration au travail quotidien du praticien.

## Parcours principaux

| Objectif du praticien | Parcours attendu | Resultat visible |
| --- | --- | --- |
| Partager un guide | Choisir "Ressource gratuite", importer le fichier, personnaliser la page, tester, publier, partager | Le visiteur recoit la ressource et peut decouvrir les accompagnements du praticien |
| Proposer une seance decouverte | Choisir "Prise de rendez-vous", associer une prestation, verifier la page, publier | Le visiteur arrive sur la bonne offre et peut reserver |
| Promouvoir un atelier | Choisir "Evenement", associer l'evenement, verifier les dates et le bouton, publier | L'evenement reste identifiable jusqu'a l'inscription |
| Faire un suivi simple | Relire les trois messages prepares, choisir ceux inclus, tester, activer | Seuls les prochains contacts concernes entrent dans le suivi |
| Ecrire a un groupe | Choisir ou creer un segment, verifier le nombre de destinataires, preparer et programmer | Le statut brouillon/programme/envoye reste explicite |
| Traiter une nouvelle demande | Ouvrir le contact, voir son origine, envoyer un email ou appeler, mettre a jour l'etape | Une action concrete remplace la simple consultation d'une fiche |
| Corriger une page publiee | Modifier le brouillon, previsualiser, republier volontairement | La version en ligne reste intacte avant republication |
| Remplacer une image | Importer JPG/PNG/WebP, decrire l'image, enregistrer, previsualiser, republier | L'image est automatiquement activee et la version publique ne change qu'apres republication |

## Constats des huit scenarios

### 1. Guide PDF gratuit

Le parcours complet fonctionne : page, collecte de l'adresse, consentement, livraison privee, confirmation et proposition d'une prochaine etape. L'ancienne confirmation s'arretait apres la livraison ; le nouveau bouton **Decouvrir mes accompagnements** evite cette impasse sans pousser artificiellement a l'achat.

### 2. Seance decouverte

La creation guidee fonctionne, y compris le choix "Je choisirai plus tard". Le brouillon explique maintenant que l'offre devra etre associee avant publication. Dans les parametres, le praticien peut choisir une prestation dont le nom, la duree et le prix sont visibles. L'isolation par praticien est verifiee cote serveur.

### 3. Evenement presentiel ou en ligne

L'espace de travail utilise maintenant **Page de l'atelier** ou le contexte reel au lieu de **Ressource**. La destination affiche l'evenement associe. Un evenement en cours ou deja configure n'est pas reinterprete par ces changements.

### 4. Suivi en trois messages

L'ecran montrait auparavant toutes les options techniques en meme temps. Il commence maintenant par l'etat reel, les destinataires potentiels, les trois messages et l'action d'activation. Le simulateur, l'utilisation mensuelle et les regles avancees restent accessibles dans des sections repliees.

### 5. Tags, segment et campagne

Le scenario est fonctionnel et isole par praticien. Les garde-fous existants restent actifs : estimation avant envoi, consentement, exclusions, limite de frequence, test adresse au praticien et statuts de campagne. La principale amelioration future sera un assistant en trois etapes plutot qu'un editeur dense.

### 6. Nouvelle demande

La fiche montre maintenant des actions directes **Envoyer un email** et **Appeler**. Les finalites de consentement internes sont traduites en francais naturel ; `request_processing` n'est plus affiche a l'utilisateur. L'origine, le parcours et l'historique restent visibles.

### 7. Brouillon et version publiee

La version publique est desormais resolue depuis son instantane publie, y compris sa destination. Modifier l'offre source ou le brouillon ne change donc plus silencieusement la page en ligne. Le bouton **Republier les modifications** n'apparait que lorsqu'une difference existe reellement.

### 8. Image et partage social

L'import JPG/PNG/WebP fonctionne avec apercu et texte alternatif obligatoire. Le test navigateur a revele puis permis de corriger un cas ou le fichier etait importe alors que la section image restait decochee. Choisir une image active maintenant automatiquement la section, egalement cote serveur sans JavaScript. Les metadonnees de partage restent separees et la publication est toujours volontaire.

## Priorites

### P0 - realise pendant cette passe

- Permettre d'associer une offre apres la creation et verifier sa propriete cote serveur.
- Utiliser la version publiee comme source de verite sur les pages publiques et pour l'attribution.
- Afficher en premier toute erreur qui bloque la publication.
- Ne plus annoncer 100 % ou "pret" lorsque la destination ou une transition est invalide.
- Distinguer clairement brouillon, modifications non publiees et version en ligne.
- Rendre la republication possible et explicite sans modifier silencieusement la page publique.
- Simplifier l'ecran des messages et expliquer exactement qui recevra quoi.
- Rendre les contacts directement actionnables.
- Eviter l'impasse apres la remise d'un guide gratuit.
- Activer automatiquement la section image lors d'un import.

### P1 - realise pendant cette passe

- Contextualiser les noms des pages selon le type d'offre.
- Montrer le libelle reel de la prestation, de l'evenement ou de la formation associee.
- Fournir une reassurance sur la version deja en ligne pendant une correction bloquante.
- Traduire les finalites et identifiants internes en vocabulaire utilisateur.
- Regrouper les reglages avances par divulgation progressive.
- Ajouter les confirmations aux actions consequentes : publication, republication, activation et mise en pause.
- Conserver les controles avances disponibles sans les imposer lors du premier passage.

### P2 - suite recommandee

1. Ajouter une checklist de premiere mise en ligne memorisee par praticien.
2. Transformer la creation d'une campagne en trois etapes : **Contenu**, **Destinataires**, **Verifier et programmer**.
3. Proposer des exemples courts par metier et objectif, sans promesse medicale.
4. Rendre le bouton secondaire de confirmation configurable : profil, rendez-vous, autre page ou aucun.
5. Ajouter une action "A faire ensuite" et un rappel date sur les contacts.
6. Proposer la fusion assistee des doublons avec comparaison avant validation.
7. Ajouter des vues de contacts enregistrees et partageables dans le cabinet.
8. Afficher des reperes de conversion uniquement apres un volume suffisant, sans faux benchmark.
9. Permettre d'enregistrer des sections de page favorites avec une bibliotheque plus visuelle.
10. Tester les libelles et l'onboarding avec cinq praticiens n'ayant jamais utilise d'outil marketing avant d'ajouter d'autres fonctions.

## Libelles francais modifies

| Avant ou ambigu | Libelle retenu |
| --- | --- |
| Parcours d'offre, utilise comme entree principale | Pages et campagnes |
| Choix reporte sans explication | Je choisirai plus tard, avec rappel de l'obligation avant publication |
| Ressource, pour une page d'evenement | Page de l'atelier / libelle contextualise |
| Activé, sur chaque message encore en brouillon | Inclure dans le suivi |
| Nouvelle campagne | Envoyer un email a un groupe |
| Reglages techniques visibles | Reglages avances du suivi |
| Simulation | Tester un scenario sans envoyer d'email |
| Usage | Voir l'utilisation mensuelle des emails |
| Statut implicite | Les messages sont encore en brouillon / Ce suivi est actif |
| Publier alors qu'une version existe | Republier les modifications |
| `request_processing` | Traitement de la demande |
| Fin du guide sans suite | Decouvrir mes accompagnements |
| Action generique sur un contact | Envoyer un email / Appeler |

## Architecture et compatibilite

- Aucun concept persiste ni aucune route existante n'a ete renomme.
- Aucun schema de base de donnees n'a ete modifie pendant cette passe.
- Les destinations publiques utilisent l'instantane de la version publiee ; les anciens cookies d'attribution sans version conservent un repli compatible.
- Les changements de brouillon ne modifient ni la version publiee, ni les contacts, ni les conversions existantes.
- L'association d'une source valide le type attendu et le `user_id` du praticien.
- Les automatismes et campagnes existants conservent leurs moteurs, statuts et limites.
- Les tests n'ont utilise que SQLite en memoire ou la base QA locale. Aucun email reel n'a ete envoye et aucune donnee de production n'a ete modifiee.

## Principaux fichiers modifies pendant la passe 2

- `app/Domain/OfferJourneys/Services/OfferJourneyAttributionContext.php`
- `app/Domain/OfferJourneys/Services/OfferJourneySourceResolver.php`
- `app/Domain/OfferJourneys/Services/OfferJourneyWorkspace.php`
- `app/Http/Controllers/OfferJourneys/OfferJourneyController.php`
- `app/Http/Controllers/OfferJourneys/OfferJourneyPageController.php`
- `app/Http/Controllers/OfferJourneys/PublicOfferJourneyController.php`
- `app/Http/Controllers/PublicTherapistController.php`
- `resources/views/offer-journeys/practitioner/_workspace-header.blade.php`
- `resources/views/offer-journeys/practitioner/_workspace-progress.blade.php`
- `resources/views/offer-journeys/practitioner/automation.blade.php`
- `resources/views/offer-journeys/practitioner/contacts/show.blade.php`
- `resources/views/offer-journeys/practitioner/create.blade.php`
- `resources/views/offer-journeys/practitioner/edit.blade.php`
- `resources/views/offer-journeys/practitioner/pages/edit.blade.php`
- `resources/views/offer-journeys/practitioner/show.blade.php`
- `resources/views/offer-journeys/public/show.blade.php`
- `resources/views/public/therapist/show.blade.php`
- `tests/Feature/OfferJourneys/OfferJourneyFoundationTest.php`
- `tests/Feature/OfferJourneys/OfferJourneyGrowthEditorTest.php`
- `tests/Feature/OfferJourneys/OfferJourneyProductExperienceTest.php`

Le depot contenait deja d'autres changements non valides lies au premier redesign. Ils ont ete conserves et n'ont pas ete annules. Les fichiers `marketing/` non lies a cet audit n'ont pas ete modifies.

## Preuves visuelles

### Avant

- [Espace evenement incoherent](parcours-offres-usability-pass-2-2026-08-04/screenshots/before/01-workspace-event.png)
- [Parametres sans choix de source](parcours-offres-usability-pass-2-2026-08-04/screenshots/before/02-settings-no-source.png)
- [Ecran des messages surcharge](parcours-offres-usability-pass-2-2026-08-04/screenshots/before/03-messages-overload.png)
- [Contact sans actions directes](parcours-offres-usability-pass-2-2026-08-04/screenshots/before/04-contact-actions.png)
- [Confirmation sans prochaine etape](parcours-offres-usability-pass-2-2026-08-04/screenshots/before/05-thank-you.png)

### Apres

- [Espace evenement et prochaine action](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/01-workspace-event.png)
- [Association de l'offre dans les parametres](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/02-settings-source.png)
- [Messages avec divulgation progressive](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/03-messages-progressive.png)
- [Contact avec email et telephone](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/04-contact-actions.png)
- [Guide livre avec prochaine etape](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/05-thank-you-next-step.png)
- [Espace de travail mobile](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/06-workspace-mobile.png)
- [Messages mobile](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/07-messages-mobile.png)
- [Contact mobile](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/08-contact-mobile.png)
- [Confirmation mobile](parcours-offres-usability-pass-2-2026-08-04/screenshots/after/09-thank-you-mobile.png)

## Validation effectuee

### Tests automatises

- Suite complete `tests/Feature/OfferJourneys` : **92 tests, 558 assertions, tous reussis**.
- Tests lies aux evenements, rendez-vous, liens publics, partenaire et emails en file : **19 tests, 141 assertions, tous reussis**.
- Test cible image et experience produit : **18 tests, 134 assertions, tous reussis**.
- Compilation des vues Blade : reussie.
- `npm run build` : reussi avec Vite 5.4.9.

Tous les tests Laravel ont utilise `TEST_DB_CONNECTION=sqlite` et `TEST_DB_DATABASE=:memory:`. La protection du projet a refuse la premiere tentative qui pointait vers MySQL ; aucun test destructif n'a ete execute sur cette base.

### Verification navigateur

Seize ecrans ont ete verifies a chacun des trois formats, soit 48 controles de page : entree du module, creation, espace de travail, editeur, partage, messages, campagnes, editeur d'email, liste et fiche contact, segments, pipeline, import, resultats, utilisation et confirmation publique.

- 390 x 844 : aucun debordement global, aucun controle casse.
- 768 x 1024 : aucun debordement global, aucun controle casse.
- 1440 x 1000 : aucun debordement global, aucun controle casse.
- Navigation clavier et focus visible verifies sur l'ecran mobile des messages.
- Erreurs de validation testees dans la section concernee.
- Confirmations verifiees pour publication, republication, activation et pause.
- Import d'image reel verifie : apercu, activation automatique, enregistrement et restitution du chemin prive.

## Decisions metier restantes

- Choisir si **Pages et campagnes** devient le nom definitif partout dans le produit et la documentation.
- Decider si le bouton secondaire apres un guide doit toujours mener au profil ou etre configurable.
- Fournir des exemples valides par metier pour enrichir la bibliotheque de modeles.
- Definir le niveau d'accompagnement a la premiere campagne : tutoriel court, checklist ou rendez-vous d'onboarding.
- Choisir les indicateurs qui constituent un succes commercial pour chaque objectif : demande, reservation, inscription ou revenu.
- Faire valider les textes de consentement et de conservation dans le chantier juridique dedie ; ce point et la delivrabilite SES etaient volontairement hors du perimetre produit de cette passe.

## Conclusion sceptique

Oui, un praticien non technique peut maintenant comprendre la proposition en moins de dix secondes, choisir un cas d'usage, creer une page, corriger un blocage, tester et publier sans connaitre le vocabulaire des tunnels de vente. Il peut aussi retrouver un contact et agir directement.

La limite restante n'est plus un defaut structurel du parcours principal. Elle concerne surtout la premiere campagne segmentée et les automatismes avances : ils sont maintenant moins intimidants, mais une checklist contextuelle et un assistant de programmation augmenteraient encore fortement l'activation. Le bon prochain investissement est donc l'accompagnement de la premiere reussite, pas l'ajout d'une nouvelle couche de fonctions.
