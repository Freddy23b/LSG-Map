<?php
/*
Plugin Name: LSG Map
Plugin URI:
Description: Une map Les SALLES gosses personnalisée, sous forme de widget. Ce widget est également insérable via le shortcode "[lsgmap_shortcode]".
Version: 0.1
Author: Les SALLES gosses
Author URI: https://www.lessallesgosses.fr/
License: GPL2
*/


/**
* Class racine du plugin LSG Map (instancie la class "Lsg_Map")
*/
class Lsg_Map_Link
{
	public function __construct()
	{
		/////   INSTANCIATION OBJET "Lsg_Map"   /////

		// On instancie un objet "Lsg_Map" afin de pouvoir utiliser ses fonctionnalités :
		// on inclut "lsgmap.php" :
		include_once plugin_dir_path( __FILE__ ).'/lsgmap.php';
		// pour ensuite créer une nouvelle instance de "Lsg_Map" (définie dans le fichier ci-dessus) :
		new Lsg_Map();


        /////   CHARGEMENT STYLE.CSS TB / DASH   /////

        // Dès l'instance de la présente class "Lsg_Map_Link" :  Au HOOK "admin_enqueue_scripts" :
        // On lance la method chargeant la bonne feuille de style.css ("lsgmap_dash_css_load()") :
        // (ceci remplacerait, en procédural : "add_action( 'admin_enqueue_scripts', 'lsgmap_dash_css_load' );")
        add_action( 'admin_enqueue_scripts', array($this, 'lsgmap_dash_css_load') );


		/////   DB   /////

		// CREATION TABLE :
		// Commentaires : cf. plugin "lsgsmallads".
		register_activation_hook( __FILE__, array('Lsg_Map', 'createLsgmapTable') );

		// SUPPRESSION TABLE :
		// au HOOK DESINSTALLATION du plugin, lancer la method "dropLsgmapTable" :
		register_uninstall_hook( __FILE__, array('Lsg_Map', 'dropLsgmapTable') );

	}// end "function __construct()"




    /////   CHARGEMENT STYLE.CSS TB   /////

    public function lsgmap_dash_css_load()
    {
        // Charger la bonne feuille de style.css (1er arg : le nom à donner au fichier style.css ; 2ème : chemin vers ce fichier) :
        wp_enqueue_style( 'lsgmap_dash_css_load', plugins_url( 'css/styledash.css', __FILE__ ) );
    }

}// class Lsg_Map_Link

// Instance de cette class pour qu'elle fonctionne :
new Lsg_Map_Link();