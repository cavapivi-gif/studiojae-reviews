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

export const SOURCE_HEX = {
  google:      '#4285F4',
  tripadvisor: '#00AF87',
  facebook:    '#1877F2',
  trustpilot:  '#00B67A',
  regiondo:    '#e85c2c',
  direct:      '#374151',
  autre:       '#9CA3AF',
}

export const SEASONS = {
  spring: { label: 'Printemps', months: [3, 4, 5] },
  summer: { label: 'Été', months: [6, 7, 8] },
  autumn: { label: 'Automne', months: [9, 10, 11] },
  winter: { label: 'Hiver', months: [12, 1, 2] },
}

export const SOURCE_OPTIONS = [
  { value: '', label: 'Toutes les sources' },
  ...Object.entries(SOURCE_LABELS).map(([v, l]) => ({ value: v, label: l })),
]
