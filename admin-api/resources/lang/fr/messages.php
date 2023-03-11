<?php
return [
    
    /**
    * Success messages
    */
    'success' => [
        'MESSAGE_TENANT_CREATED' => 'Locataire créé avec succès',
        'MESSAGE_TENANT_UPDATED' => 'Détails du locataire mis à jour avec succès',
        'MESSAGE_TENANT_DELETED' => 'Locataire supprimé avec succès',
        'MESSAGE_TENANT_LISTING' => 'Locataires listés avec succès',
        'MESSAGE_NO_RECORD_FOUND' => 'Aucun enregistrement trouvé',
        'MESSAGE_TENANT_FOUND' => 'Locataire trouvé avec succès',
        'MESSAGE_TENANT_API_USER_LISTING' => 'Les utilisateurs de l\'API du locataire répertoriés avec succès',        
        'MESSAGE_API_USER_FOUND' => 'Utilisateur de l\'API trouvé avec succès',
        'MESSAGE_API_USER_CREATED_SUCCESSFULLY' => 'Utilisateur de l\'API créé avec succès',
        'MESSAGE_API_USER_DELETED' => 'Utilisateur de l\'API supprimé avec succès',
        'MESSAGE_API_USER_UPDATED_SUCCESSFULLY' => 'La clé secrète de l\'utilisateur de l\'API a été mise à jour avec succès',
        'MESSAGE_ALL_SETTING_LISTING' => 'Tous les paramètres listés avec succès',
        'MESSAGE_LANGUAGE_FOUND' => 'Langue trouvée avec succès',
        'MESSAGE_LANGUAGE_LISTING' => 'Langues listées avec succès',
        'MESSAGE_LANGUAGE_CREATED' => 'Langue ajoutée avec succès',
        'MESSAGE_LANGUAGE_UPDATED' => 'Détails de la langue mis à jour avec succès',
        'MESSAGE_NEWS_DELETED' => 'Langue supprimée avec succès',
        'MESSAGE_TENANT_LANGUAGE_ADDED' => 'Langue locataire ajoutée avec succès',
        'MESSAGE_TENANT_LANGUAGE_UPDATED' => 'Langue du locataire mise à jour avec succès',
        'MESSAGE_TENANT_LANGUAGE_LISTING' => 'Langues locataires répertoriées avec succès',
        'MESSAGE_TENANT_LANGUAGE_DELETED' => 'Langue locataire supprimée avec succès',
        'MESSAGE_NO_ACTIVITY_LOGS_ENTRIES_FOUND' => 'Aucun journal d\'activité trouvé',
        'MESSAGE_ACTIVITY_LOGS_ENTRIES_LISTING' => 'Les journaux d\'activité répertoriés avec succès',
        'MESSAGE_MIGRATION_CHANGES_APPLIED_SUCCESSFULLY' => 'Les modifications de migration ont été appliquées avec succès sur la base de données client hébergée.',
        'MESSAGE_SEEDER_CHANGES_APPLIED_SUCCESSFULLY' => 'Les modifications de migration ont été appliquées avec succès sur la base de données client.',
        'MESSAGE_TENANT_BACKGROUND_PROCESS_COMPLETED' => 'Le processus d\'arrière-plan du locataire s\'est terminé avec succès'
    ],
    
    /**
    * API Error Codes and Message
    */
    'custom_error_message' => [
        'ERROR_TENANT_REQUIRED_FIELDS_EMPTY' => 'Le nom ou le champ sponsorisé est vide',
        'ERROR_TENANT_ALREADY_EXIST' => 'Le nom du locataire est déjà pris, veuillez essayer avec un nom différent',
        'ERROR_TENANT_NOT_FOUND' => 'Non trouvé dans le système',
        'ERROR_DATABASE_OPERATIONAL' => 'Erreur opérationnelle de la base de données',
        'ERROR_NO_DATA_FOUND' => 'Aucune donnée disponible',
        'ERROR_INVALID_ARGUMENT' => 'argument invalide',
        'ERROR_API_USER_NOT_FOUND' => 'Utilisateur API introuvable',
        'ERROR_INVALID_JSON' => 'Format Json invalide',
        'ERROR_LANGUAGE_NOT_FOUND' => 'Langue non trouvée dans le système',
        'ERROR_TENANT_LANGUAGE_NOT_FOUND' => 'Langue du locataire introuvable dans le système',
        'ERROR_TENANT_DEFAULT_LANGUAGE_REQUIRED' => 'Au moins une langue par défaut est requise',
        'ERROR_INVALID_MIGRATION_FILE_EXTENSION' => 'extension de fichier invalide',
        'ERROR_DELETE_DEFAULT_TENANT_LANGUAGE' => 'Vous ne pouvez pas supprimer la langue du locataire par défaut',
        'ERROR_INVALID_FQDN_NAME' => 'Nom de locataire non valide',
        'ERROR_LANGUAGE_UNABLE_TO_DELETE' => 'La langue ne peut pas être supprimée car elle est actuellement utilisée.'
    ]
    
];
