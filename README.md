# Météo des plages pour Jeedom

Plugin Jeedom affichant la météo terrestre, les conditions marines et les prochaines marées dans un widget responsive.

## Fonctionnalités

- météo actuelle, températures minimale et maximale ;
- température ressentie, humidité, précipitations, risque de pluie et indice UV ;
- vitesse, direction et rafales du vent ;
- température de la mer, hauteur, période et direction des vagues et de la houle ;
- quatre prochaines marées modélisées ;
- plages prédéfinies autour de Royan ou coordonnées personnalisées ;
- image de fond personnalisable ;
- actualisation automatique toutes les 30 minutes et rafraîchissement manuel intégré.

## Compatibilité

- Jeedom 4.4 ou supérieur ;
- aucune clé API nécessaire ;
- données fournies par Open-Meteo et Open-Meteo Marine.

## Installation et mises à jour depuis GitHub

Dans Jeedom, ouvrez **Réglages → Système → Centre de mise à jour**, puis ajoutez une source GitHub :

- **ID logique** : `meteodesplages`
- **Utilisateur** : `EricBoinaud`
- **Dépôt** : `meteodesplages`
- **Branche** : `main`

Le contenu du plugin doit être placé directement à la racine du dépôt : `core`, `desktop`, `docs`, `plugin_info`, etc.

## Avertissement sur les marées

Les horaires, hauteurs et coefficients affichés sont issus d’un modèle et restent indicatifs. Ils ne doivent pas être utilisés pour la navigation. Le SHOM demeure la référence officielle.

## Licence

AGPL-3.0-or-later.
