# Roadmap proposée - Parcours d’offre

## État d’avancement au 11 juillet 2026

Les éléments P0 et P1 décrits ci-dessous sont maintenant implémentés derrière des drapeaux indépendants, tous désactivés par défaut. Ils restent à valider en pilote avant une ouverture large.

- **Implémenté et testé:** SES/SNS, suppressions, diagnostic DNS, limites progressives, console support, conservation configurable, checklist de publication, modèles métier, éditeur enrichi, assistant de rédaction, formulaires bornés, aperçu et test email, campagnes planifiées, relance d’abandon, vue contact, pipeline et import CSV.
- **Validation métier requise:** textes juridiques définitifs, durées de conservation, seuils de réputation, modèles email, délai de relance et critères d’ouverture du pilote.
- **Non activé automatiquement:** aucun drapeau de production n’est modifié par le déploiement du code.

Cette roadmap classe les améliorations par valeur pour les praticiens, risque opérationnel et dépendances. Elle ne signifie pas que tout doit être construit immédiatement.

## P0 - Avant une ouverture large

### Délivrabilité et sécurité email

- recevoir les événements de rejet, bounce et plainte d’Amazon SES;
- ajouter automatiquement les adresses concernées à la liste de suppression;
- afficher un diagnostic simple de délivrabilité à l’équipe Olithea;
- vérifier SPF, DKIM et DMARC pour `olithea.fr`;
- définir des limites progressives selon l’ancienneté et la réputation du compte.

**Pourquoi:** une mauvaise délivrabilité peut affecter tous les emails Olithea, pas uniquement les parcours.

### Console interne de support

- rechercher un parcours, un contact, une exécution ou un message;
- voir les raisons d’arrêt et d’échec en français;
- mettre en pause un parcours ou les messages d’un praticien;
- relancer une exécution sûre ou la réconciliation;
- journaliser les actions de support.

**Pourquoi:** le pilote doit pouvoir être diagnostiqué sans accès direct à la base.

### Validation juridique et conservation

- valider les textes de confidentialité et de consentement;
- définir les durées de conservation des contacts et événements analytiques;
- automatiser l’expiration ou l’anonymisation selon la politique retenue;
- documenter le rôle du praticien et celui d’Olithea.

### Aide au démarrage

- ajouter une checklist intégrée de première publication;
- proposer des exemples réels par métier et objectif;
- afficher un contrôle avant publication avec erreurs et recommandations;
- ajouter un lien vers le guide depuis chaque écran du module.

## P1 - Améliorer l’activation et la qualité des pages

### Bibliothèque de modèles métier

- séance découverte;
- bilan initial;
- atelier en présentiel;
- conférence en ligne;
- guide gratuit;
- mini-programme email;
- formation digitale;
- bon cadeau saisonnier;
- demande de rappel.

Chaque modèle devrait fournir une structure et des exemples, sans inventer de promesse médicale.

### Éditeur visuel plus riche

- ajout et réorganisation de blocs par glisser-déposer;
- image principale, galerie, vidéo, témoignages, intervenant, tarif et encadré pratique;
- aperçu ordinateur/téléphone côte à côte;
- styles limités issus de la marque du praticien;
- bibliothèque de sections réutilisables.

### Assistant de rédaction

- proposer plusieurs titres à partir de l’offre existante;
- reformuler les textes trop vagues;
- signaler les promesses risquées ou médicales;
- vérifier longueur, lisibilité et cohérence du bouton;
- toujours laisser le praticien valider avant publication.

### Formulaires configurables

- jusqu’à trois questions personnalisées non médicales;
- choix unique, choix multiple et texte court;
- logique conditionnelle simple;
- motif et finalité visibles pour chaque champ;
- modèles de qualification selon l’objectif.

## P1 - Améliorer les messages

### Éditeur de messages plus accessible

- prévisualisation réelle de l’email;
- envoi d’un message test au praticien;
- bibliothèque de modèles sobres;
- aperçu des variables remplacées;
- avertissement si un lien ou un objet manque;
- indicateur du nombre de destinataires concernés.

### Relance d’abandon

- détecter une réservation ou un paiement commencé mais non terminé;
- attendre un délai raisonnable;
- envoyer une seule relance si le consentement et la base légale le permettent;
- arrêter immédiatement après réservation, achat, annulation ou désinscription.

### Calendrier de campagnes

- préparer une campagne à une date future;
- réutiliser une campagne sur plusieurs parcours;
- visualiser les messages prévus cette semaine;
- éviter que plusieurs parcours contactent la même personne trop fréquemment.

## P1 - Améliorer le suivi commercial

### Vue contact unifiée

- détecter et proposer la fusion de doublons;
- afficher rendez-vous, achats et inscriptions reliés sans mélanger les notes cliniques;
- historique complet des sources et campagnes;
- prochaine action recommandée;
- rappel depuis le tableau de bord.

### Pipeline plus pratique

- glisser-déposer entre les étapes;
- motifs de perte ou de report;
- actions groupées prudentes;
- filtres enregistrés;
- objectifs par parcours et par période.

### Import encadré

- import CSV avec aperçu et validation;
- déduplication avant import;
- preuve de consentement obligatoire pour les relances;
- rapport des lignes ignorées;
- annulation de l’import avant envoi.

## P2 - Conversion et revenus

### Vitrine d’offres

- page publique regroupant les offres actives du praticien;
- filtres par format ou besoin;
- mise en avant saisonnière;
- intégration naturelle au profil public Olithea.

### Offre complémentaire

- proposition cohérente après une réservation ou un achat;
- bon cadeau après une séance;
- formation après un atelier;
- séance individuelle après une ressource gratuite;
- règles strictes pour éviter la pression commerciale.

### Paiement avancé

- paiement en plusieurs fois lorsque l’offre le permet;
- packs et échéanciers existants comme sources de parcours;
- suivi du revenu net, remboursements et échéances;
- reprise sûre après interruption Stripe.

## P2 - Mesure et optimisation

- entonnoir visuel page par page;
- comparaison des campagnes et canaux;
- délai moyen entre première visite et conversion;
- cohortes par type d’offre;
- objectifs personnalisés;
- export des résultats;
- alertes en cas de chute anormale du taux de formulaire ou de conversion.

### Tests A/B, seulement après volume suffisant

- variante de titre, bouton ou ordre des sections;
- répartition stable et respectueuse du consentement;
- seuil statistique minimal;
- arrêt automatique d’une variante dégradée;
- aucune expérimentation sur des promesses médicales.

## P3 - Écosystème et fonctions avancées

- domaine personnalisé avec SSL automatisé;
- SMS ou WhatsApp avec consentement spécifique;
- webhooks et API pour les outils externes;
- Zapier ou Make;
- affiliation et liens ambassadeurs;
- rôles d’équipe, validation avant publication et journal d’audit;
- import guidé depuis Systeme.io;
- webinaire permanent et rediffusion;
- intégration avec les communautés Olithea;
- traduction multilingue;
- recommandations intelligentes basées sur les résultats du praticien.

## Ordre recommandé sur 12 mois

### Trimestre 1 - Fiabiliser le pilote

- SES bounce/complaint;
- console support;
- validation juridique et conservation;
- checklist intégrée;
- dix modèles métier testés avec de vrais praticiens.

### Trimestre 2 - Rendre la création nettement plus simple

- éditeur de blocs;
- aperçu côte à côte;
- questions personnalisées bornées;
- test d’email;
- pipeline amélioré.

### Trimestre 3 - Améliorer la conversion

- abandon de réservation;
- vitrine d’offres;
- campagnes planifiées;
- rapports de conversion avancés;
- offres complémentaires prudentes.

### Trimestre 4 - Ouvrir l’écosystème

- domaines personnalisés;
- API et webhooks;
- intégrations externes;
- équipe et validation;
- premiers tests A/B pour les comptes ayant assez de trafic.

## Ce qu’il vaut mieux ne pas construire trop tôt

- un éditeur HTML totalement libre, difficile à sécuriser et à maintenir sur mobile;
- des automatisations sans limite ou avec boucles;
- des campagnes SMS avant d’avoir un registre de consentement dédié;
- de l’A/B testing lorsque les parcours ont trop peu de visiteurs;
- des fonctions d’affiliation avant de fiabiliser remboursements et attribution;
- un assistant qui publie automatiquement des promesses rédigées par IA;
- une copie complète de Systeme.io sans avantage métier spécifique aux praticiens.

La priorité doit rester la même: aider un praticien à transformer une offre réelle en parcours clair, mesurable et simple à opérer, sans lui demander de devenir spécialiste du marketing technique.
