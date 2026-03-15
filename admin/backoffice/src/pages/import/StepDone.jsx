import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../../lib/api'
import { IconRocket, IconMapPin } from '../../components/Icons'

/* ═══════════════════════════════════════════════════════════════════
   STEP 4 : Terminé — summary & next actions
   ═══════════════════════════════════════════════════════════════════ */
export default function StepDone() {
  const navigate = useNavigate()
  const [stats, setStats] = useState(null)

  useEffect(() => {
    api.dashboard().then(setStats).catch(() => {})
  }, [])

  return (
    <div className="max-w-lg mx-auto text-center py-8">
      <div className="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
        <IconRocket size={28} className="text-emerald-600" />
      </div>
      <h2 className="text-xl font-bold mb-2">Configuration terminée</h2>
      <p className="text-sm text-gray-500 mb-6">
        Votre plugin SJ Reviews est prêt. Voici ce que vous pouvez faire ensuite :
      </p>

      {stats && (
        <div className="flex justify-center gap-6 mb-8">
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.total ?? 0}</p>
            <p className="text-xs text-gray-500">Avis</p>
          </div>
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.avg ? Number(stats.avg).toFixed(1) : '—'}</p>
            <p className="text-xs text-gray-500">Note moyenne</p>
          </div>
          <div className="text-center">
            <p className="text-2xl font-bold text-gray-900">{stats.sources ? Object.keys(stats.sources).length : 0}</p>
            <p className="text-xs text-gray-500">Sources</p>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
        <ActionTile
          icon={<IconStar2 size={18} />}
          title="Voir les avis"
          description="Gérez et modérez vos avis importés."
          onClick={() => navigate('/reviews')}
        />
        <ActionTile
          icon={<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>}
          title="Dashboard"
          description="Statistiques et tendances en temps réel."
          onClick={() => navigate('/')}
        />
        <ActionTile
          icon={<IconMapPin size={18} />}
          title="Gérer les lieux"
          description="Lancer une synchronisation Google/Trustpilot."
          onClick={() => navigate('/lieux')}
        />
        <ActionTile
          icon={<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>}
          title="Réglages avancés"
          description="Apparence, critères, shortcodes et plus."
          onClick={() => navigate('/settings/display')}
        />
      </div>
    </div>
  )
}

function ActionTile({ icon, title, description, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex items-start gap-3 border border-gray-200 rounded-lg p-4 text-left hover:border-gray-300 hover:bg-gray-50/50 transition-colors"
    >
      <span className="text-gray-500 mt-0.5">{icon}</span>
      <div>
        <p className="text-sm font-semibold text-gray-900">{title}</p>
        <p className="text-xs text-gray-500">{description}</p>
      </div>
    </button>
  )
}

// Import IconStar for the done step
function IconStar2({ size = 16, ...props }) {
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" {...props}>
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>
  )
}
