import { useState } from 'react'
import { NavLink, useLocation } from 'react-router-dom'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubItem,
  SidebarMenuSubButton,
  SidebarRail,
} from '@/components/ui/sidebar'
import {
  IconDashboard, IconStar, IconPlus, IconSettings, IconExternalLink,
  IconMapPin, IconRocket, IconChevronRight, IconLayers,
  IconKey, IconPalette, IconSliders, IconLink, IconCode,
} from './Icons'

/* ── Navigation tree ─────────────────────────────────────── */
const navMain = [
  { to: '/dashboard',   label: 'Tableau de bord', icon: IconDashboard },
  { to: '/reviews',     label: 'Tous les avis',   icon: IconStar },
  { to: '/reviews/new', label: 'Ajouter un avis', icon: IconPlus },
  { to: '/lieux',       label: 'Lieux',            icon: IconMapPin },
  { to: '/providers',   label: 'Providers',        icon: IconLayers },
  { to: '/import',      label: 'Onboarding',       icon: IconRocket },
  {
    label: 'Réglages',
    icon: IconSettings,
    children: [
      { to: '/settings/api',        label: 'API & Sync',   icon: IconKey },
      { to: '/settings/display',    label: 'Affichage',    icon: IconPalette },
      { to: '/settings/criteria',   label: 'Critères',     icon: IconSliders },
      { to: '/settings/links',      label: 'Liaisons',     icon: IconLink },
      { to: '/settings/shortcodes', label: 'Shortcodes',   icon: IconCode },
    ],
  },
  { to: '/docs', label: 'Documentation', icon: IconExternalLink },
]

export default function AppSidebar(props) {
  const location = useLocation()
  const adminUrl = window.sjReviews?.admin_url ?? ''

  const isActive = (to) => {
    if (to === '/reviews') return location.pathname === '/reviews'
    return location.pathname.startsWith(to)
  }

  // Settings sub-tree is open if any settings route is active
  const settingsOpen = location.pathname.startsWith('/settings')
  const [forceOpen, setForceOpen] = useState(null)
  const isSettingsExpanded = forceOpen !== null ? forceOpen : settingsOpen

  return (
    <Sidebar collapsible="icon" {...props}>
      {/* Logo */}
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <NavLink to="/dashboard">
                <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                  <IconStar size={16} strokeWidth={1.5} />
                </div>
                <div className="flex flex-col gap-0.5 leading-none">
                  <span className="font-semibold">SJ Reviews</span>
                  <span className="text-xs text-muted-foreground">Plugin</span>
                </div>
              </NavLink>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      {/* Navigation */}
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Navigation</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {navMain.map((item) =>
                item.children ? (
                  /* ── Collapsible tree item (Réglages) ── */
                  <SidebarMenuItem key={item.label}>
                    <SidebarMenuButton
                      tooltip={item.label}
                      isActive={settingsOpen}
                      onClick={() => setForceOpen(o => o === null ? !settingsOpen : !o)}
                    >
                      <item.icon size={16} strokeWidth={1.5} />
                      <span className="flex-1">{item.label}</span>
                      <IconChevronRight
                        size={14}
                        strokeWidth={1.5}
                        style={{
                          transition: 'transform 200ms',
                          transform: isSettingsExpanded ? 'rotate(90deg)' : 'rotate(0deg)',
                        }}
                      />
                    </SidebarMenuButton>

                    {isSettingsExpanded && (
                      <SidebarMenuSub>
                        {item.children.map(({ to, label, icon: SubIcon }) => (
                          <SidebarMenuSubItem key={to}>
                            <SidebarMenuSubButton
                              asChild
                              size="sm"
                              isActive={isActive(to)}
                            >
                              <NavLink to={to}>
                                <SubIcon size={14} strokeWidth={1.5} />
                                <span>{label}</span>
                              </NavLink>
                            </SidebarMenuSubButton>
                          </SidebarMenuSubItem>
                        ))}
                      </SidebarMenuSub>
                    )}
                  </SidebarMenuItem>
                ) : (
                  /* ── Simple nav item ── */
                  <SidebarMenuItem key={item.to}>
                    <SidebarMenuButton
                      asChild
                      isActive={isActive(item.to)}
                      tooltip={item.label}
                    >
                      <NavLink to={item.to}>
                        <item.icon size={16} strokeWidth={1.5} />
                        <span>{item.label}</span>
                      </NavLink>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                )
              )}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>

        {adminUrl && (
          <SidebarGroup>
            <SidebarGroupLabel>WordPress</SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu>
                <SidebarMenuItem>
                  <SidebarMenuButton asChild tooltip="WP Admin">
                    <a
                      href={`${adminUrl}edit.php?post_type=sj_avis`}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <IconExternalLink size={16} strokeWidth={1.5} />
                      <span>WP Admin</span>
                    </a>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        )}
      </SidebarContent>

      {/* Version footer */}
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="sm" className="text-muted-foreground pointer-events-none">
              <span className="text-xs">v{window.sjReviews?.version ?? '1.0.0'}</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>

      <SidebarRail />
    </Sidebar>
  )
}
