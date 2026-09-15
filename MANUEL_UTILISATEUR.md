# jumpseat - Manuel utilisateur

Ce manuel explique comment utiliser l'application web `jumpseat`.

## 1) Objectif

`jumpseat` aide les pilotes a partager des vols planifiés et permet aux autres utilisateurs de manifester leur intérêt pour les places disponibles.

## 2) Acces et langue

- Ouvrez l'application dans votre navigateur :
- Utilisez les boutons de langue dans la barre du haut :
  - `EN` pour l'anglais
  - `FR` pour le francais

## 3) Gestion du compte

### Inscription

1. Cliquez sur `Inscription`.
2. Renseignez :
   - Nom d'utilisateur (obligatoire, unique)
   - Email (obligatoire, unique)
   - Mot de passe (obligatoire, minimum 8 caracteres)
   - Photo (optionnelle)
   - Telephone (optionnel)
   - Me notifier des nouveaux vols (active par defaut)
3. Envoyez le formulaire.

### Connexion / Deconnexion

- Connexion : cliquez sur `Connexion`, saisissez email + mot de passe.
- Deconnexion : cliquez sur `Déconnexion` dans la barre du haut.

### Mot de passe oublie / Reinitialisation

1. Sur la page de connexion, cliquez sur `Mot de passe oublié`.
2. Entrez votre email.
3. Utilisez le lien de reinitialisation recu par email
4. Definissez un nouveau mot de passe.

### Profil

Ouvrez `Profil` pour :
- voir nom d'utilisateur/email
- changer la photo
- mettre a jour le telephone
- configurer jusqu'a 3 modeles d'aeronef
- changer le mot de passe
- activer/desactiver les notifications de nouveaux vols
- supprimer le compte

## 4) Creation et modification des vols

### Creer un vol

1. Cliquez sur `Nouveau vol` dans la barre du haut, ou cliquez sur une case vide du calendrier.
2. Renseignez les champs obligatoires :
   - Titre du vol
   - Origine
   - Destination
   - Aeronef (liste issue des aeronefs du profil)
   - Depart prevu date/heure (local)
   - Retour prevu date/heure (local)
   - Duree de vol prevue (minutes, pas de 15)
   - Nombre de places disponibles (0 a 3)
   - Informations complementaires (optionnel)
3. Cliquez sur `Sauvegarder`.

Remarques :
- Création depuis le menu : date du jour pré-remplie avec départ `08:00` et retour `17:00`.
- Création depuis un clic sur jour : la date cliquée est pré-remplie avec les mêmes heures.

### Modifier / Supprimer vos vols

- Dans les cartes d'evenement du calendrier, utilisez `Modifier` et `Supprimer`.
- Ou ouvrez la page `Mes vols` et utilisez les mêmes actions.

## 5) Utilisation du calendrier

### Navigation hebdomadaire

- `Précedent` et `Suivant` changent de semaine.
- `Aujourd'hui` revient à la semaine en cours.

### Evenements

- Les évenements affichent :
  - Titre
  - Trajet
  - Aéronef
  - Par (nom d'utilisateur)
  - Places
- Une couleur est attribuee par proprietaire d'evenement.
- Les vols avec 0 place sont grisés.

### Fenetre de details du vol

- Cliquez sur un evenement pour ouvrir les details.
- La fenêtre inclut la photo du propriétaire (si disponible).
- `Contact` ouvre la fenetre de notification d'interet (activee seulement si places > 0).

## 6) Notifications d'interêt

1. Ouvrez les details d'un vol.
2. Cliquez sur `Contact`.
3. Saisissez votre message.
4. Cliquez sur `Envoyer`.

Le proprietaire du vol recoit une notification par email.

## 7) Notifications de nouveaux vols

Quand un utilisateur cree un vol, les utilisateurs ayant active les notifications recoivent un email avec les details du vol.

## 8) Avertissement

- Utilisez le bouton `Disclaimer` en bas de page.

## 9) Fonctions administrateur

Les utilisateurs avec privileges admin peuvent ouvrir `Admin` et :
- lister les utilisateurs
- bloquer/debloquer des utilisateurs
- supprimer des utilisateurs non-admin
- reinitialiser le mot de passe d'un utilisateur

