export const SOURCE_LABELS = {
  google:      'Google',
  tripadvisor: 'TripAdvisor',
  facebook:    'Facebook',
  trustpilot:  'Trustpilot',
  regiondo:    'Regiondo',
  direct:      'Direct',
  autre:       'Autre',
}

export const SOURCE_COLORS = {
  google:      'bg-blue-500',
  tripadvisor: 'bg-emerald-500',
  facebook:    'bg-blue-700',
  trustpilot:  'bg-green-600',
  regiondo:    'bg-orange-500',
  direct:      'bg-gray-600',
  autre:       'bg-gray-400',
}

export const SOURCE_OPTIONS = [
  { value: '', label: 'Toutes les sources' },
  ...Object.entries(SOURCE_LABELS).map(([v, l]) => ({ value: v, label: l })),
]
