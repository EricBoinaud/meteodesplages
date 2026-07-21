# Météo des plages

Plugin Jeedom affichant la météo et l'état de la mer d'une plage grâce aux API Open-Meteo, sans clé API.

## Installation

1. Installez le plugin puis activez-le.
2. Ouvrez **Plugins > Météo > Météo des plages**.
3. Ajoutez un équipement nommé **Pontaillac**.
4. Laissez les coordonnées proposées : latitude `45.6267`, longitude `-1.0518`.
5. Activez et rendez visible l'équipement, puis sauvegardez.
6. Cliquez sur la commande **Rafraîchir** pour obtenir immédiatement les premières valeurs.

Le plugin se met ensuite à jour toutes les 30 minutes.

## Données disponibles

Température extérieure et ressentie, humidité, conditions, vent et rafales, précipitations, UV, températures minimale et maximale, risque de pluie, température de la mer, hauteur/période/direction des vagues et de la houle.

## Remarque

Les données marines sont des estimations issues de modèles. Elles ne remplacent pas les bulletins officiels pour la navigation ou la sécurité en mer.


## Widget version 2

Le widget responsive regroupe la météo, le vent, les vagues, la houle et la température de la mer dans une carte moderne. Il s'adapte au tableau de bord sur ordinateur, tablette et téléphone.


## Version 2.1.0

- choix rapide parmi plusieurs plages de Royan Atlantique ;
- image intégrée pour Pontaillac ;
- URL d’image personnalisable ;
- quatre prochaines marées, hauteurs et coefficients estimés.

Les horaires et coefficients sont calculés à partir du niveau marin modélisé par Open-Meteo. Ils sont indicatifs et ne doivent pas être utilisés pour la navigation.


## Version 2.4.0
- Ajout du jour/date sur chaque prochaine marée.
- Mention plus explicite du caractère modélisé des marées Open-Meteo.


## Marées officielles SHOM
Dans l’équipement, choisissez **Source des marées → SHOM — vignette officielle**, puis indiquez le code du port (par défaut `ROYAN`). Le widget affiche alors la grande vignette officielle du SHOM avec jours, heures, hauteurs et coefficients.
