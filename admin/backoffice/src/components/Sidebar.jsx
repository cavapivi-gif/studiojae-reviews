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
  SidebarRail,
} from '@/components/ui/sidebar'
import { IconDashboard, IconStar, IconPlus, IconSettings, IconExternalLink, IconMapPin, IconUpload } from './Icons'

const navMain = [
  { to: '/dashboard',   label: 'Tableau de bord', icon: IconDashboard },
  { to: '/reviews',     label: 'Tous les avis',   icon: IconStar },
  { to: '/reviews/new', label: 'Ajouter un avis', icon: IconPlus },
  { to: '/lieux',       label: 'Lieux',            icon: IconMapPin },
  { to: '/import',      label: 'Import CSV',       icon: IconUpload },
  { to: '/settings',    label: 'Réglages',          icon: IconSettings },
  { to: '/docs',        label: 'Documentation',    icon: IconExternalLink },
]

export default function AppSidebar(props) {
  const location = useLocation()
  const adminUrl = window.sjReviews?.admin_url ?? ''

  const isActive = (to) => {
    if (to === '/reviews') return location.pathname === '/reviews'
    return location.pathname.startsWith(to)
  }

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
              {navMain.map(({ to, label, icon: Icon }) => (
                <SidebarMenuItem key={to}>
                  <SidebarMenuButton
                    asChild
                    isActive={isActive(to)}
                    tooltip={label}
                  >
                    <NavLink to={to}>
                      <Icon size={16} strokeWidth={1.5} />
                      <span>{label}</span>
                    </NavLink>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
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
