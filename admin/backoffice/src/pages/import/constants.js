import { IconKey, IconMapPin, IconUpload, IconRocket } from '../../components/Icons'

/* ── Constantes ──────────────────────────────────────────────────── */
export const ONBOARDING_STEPS = [
  { id: 'sources', label: 'Sources API', icon: IconKey },
  { id: 'lieux',   label: 'Lieux',       icon: IconMapPin },
  { id: 'import',  label: 'Import CSV',  icon: IconUpload },
  { id: 'done',    label: 'Terminé',     icon: IconRocket },
]

export const CSV_STEPS = ['Fichier', 'Colonnes', 'Défauts', 'Produits', 'Aperçu']

// Champs SJ disponibles pour le mapping
export const SJ_FIELDS = [
  { value: '_ignore',      label: '— Ignorer —' },
  { value: 'product',      label: 'Produit (nom)' },
  { value: 'order_id',     label: 'N° de commande' },
  { value: 'booking_date', label: 'Date de réservation' },
  { value: 'visit_date',   label: 'Date de l\'évènement' },
  { value: 'eval_date',    label: 'Date d\'évaluation' },
  { value: 'author',       label: 'Nom du client' },
  { value: 'email',        label: 'Email client' },
  { value: 'phone',        label: 'Téléphone' },
  { value: 'rating',       label: 'Note (1–5)' },
  { value: 'title',        label: 'Résumé / Titre' },
  { value: 'text',         label: 'Texte de l\'avis' },
]

export const AUTO_DETECT_RULES = [
  { patterns: ['email', 'mail', 'courriel'], field: 'email' },
  { patterns: ['phone', 'téléphone', 'telephone', 'mobile'], field: 'phone' },
  { patterns: ['commande', 'order', 'n°', 'numéro', 'numero', 'booking_id'], field: 'order_id' },
  { required: ['date'], patterns: ['réservation', 'reservation', 'booking'], field: 'booking_date' },
  { required: ['date'], patterns: ['évènement', 'evenement', 'visite', 'event'], field: 'visit_date' },
  { required: ['date'], patterns: ['évaluation', 'evaluation', 'avis', 'submitted', 'eval'], field: 'eval_date' },
  { patterns: ['nom', 'name', 'auteur', 'author', 'prénom'], field: 'author' },
  { patterns: ['résumé', 'resume', 'summary', 'titre', 'title'], field: 'title' },
  { patterns: ['note', 'rating', 'étoile', 'star', 'score'], field: 'rating' },
  { patterns: ['texte', 'text', 'commentaire', 'comment', 'avis', 'review', 'évaluation', 'evaluation'], field: 'text' },
  { patterns: ['produit', 'product', 'excursion', 'service'], field: 'product' },
]

export const SOURCE_OPTIONS = [
  { value: 'google',      label: 'Google' },
  { value: 'tripadvisor', label: 'TripAdvisor' },
  { value: 'facebook',    label: 'Facebook' },
  { value: 'trustpilot',  label: 'Trustpilot' },
  { value: 'regiondo',    label: 'Regiondo' },
  { value: 'direct',      label: 'Direct' },
  { value: 'autre',       label: 'Autre' },
]

export const EMPTY_LIEU = { name: '', place_id: '', source: 'google', address: '', active: true, trustpilot_domain: '', tripadvisor_location_id: '' }
