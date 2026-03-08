import { NavLink } from 'react-router-dom'
import {
  LayoutDashboard,
  Star,
  Plus,
  Settings,
  ExternalLink,
  MapPin,
} from 'lucide-react'

const nav = [
  { to: '/dashboard',   label: 'Tableau de bord', icon: LayoutDashboard },
  { to: '/reviews',     label: 'Tous les avis',   icon: Star },
  { to: '/reviews/new', label: 'Ajouter un avis', icon: Plus },
  { to: '/lieux',       label: 'Lieux',            icon: MapPin },
  { to: '/settings',   label: 'Réglages',          icon: Settings },
  { to: '/docs',        label: 'Documentation',    icon: ExternalLink },
]

export default function Sidebar() {
  const adminUrl = window.sjReviews?.admin_url ?? ''

  return (
    <aside className="w-52 shrink-0 border-r border-gray-200 min-h-screen flex flex-col bg-white">
      {/* Logo */}
      <div className="flex items-center gap-2 px-5 py-5 border-b border-gray-200">
        <Star size={15} strokeWidth={1.5} />
        <span className="text-sm tracking-widest uppercase">SJ Reviews</span>
      </div>

      {/* Nav */}
      <nav className="flex-1 py-4">
        {nav.map(({ to, label, icon: Icon }) => (
          <NavLink
            key={to}
            to={to}
            end={to === '/reviews'}
            className={({ isActive }) =>
              `flex items-center gap-3 px-5 py-2.5 text-sm transition-colors ${
                isActive
                  ? 'bg-black text-white'
                  : 'text-gray-600 hover:text-black hover:bg-gray-50'
              }`
            }
          >
            <Icon size={15} strokeWidth={1.5} />
            {label}
          </NavLink>
        ))}

        {adminUrl && (
          <a
            href={`${adminUrl}edit.php?post_type=sj_avis`}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-400 hover:text-black transition-colors"
          >
            <ExternalLink size={13} strokeWidth={1.5} />
            WP Admin
          </a>
        )}
      </nav>

      {/* Version */}
      <div className="px-5 py-4 border-t border-gray-200">
        <span className="text-xs text-gray-400">
          v{window.sjReviews?.version ?? '1.0.0'}
        </span>
      </div>
    </aside>
  )
}
