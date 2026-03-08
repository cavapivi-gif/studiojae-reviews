import { NavLink } from 'react-router-dom'
import {
  HomeSimpleDoor,
  StarHalfDashed,
  Plus,
  Settings,
  OpenNewWindow,
  MapPin,
} from 'iconoir-react'

const nav = [
  { to: '/dashboard',   label: 'Tableau de bord', icon: HomeSimpleDoor },
  { to: '/reviews',     label: 'Tous les avis',   icon: StarHalfDashed },
  { to: '/reviews/new', label: 'Ajouter un avis', icon: Plus },
  { to: '/lieux',       label: 'Lieux',            icon: MapPin },
  { to: '/settings',   label: 'Réglages',          icon: Settings },
]

export default function Sidebar() {
  const adminUrl = window.sjReviews?.admin_url ?? ''

  return (
    <aside className="w-52 shrink-0 border-r border-gray-200 min-h-screen flex flex-col bg-white">
      {/* Logo */}
      <div className="flex items-center gap-2 px-5 py-5 border-b border-gray-200">
        <StarHalfDashed width={16} height={16} strokeWidth={1.5} />
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
            <Icon width={15} height={15} strokeWidth={1.5} />
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
            <OpenNewWindow width={13} height={13} strokeWidth={1.5} />
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
