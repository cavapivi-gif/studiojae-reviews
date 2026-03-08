import Sidebar from './Sidebar'

export default function Layout({ children }) {
  return (
    <div style={{ display: 'flex', height: '100%', background: '#fff' }}>
      <Sidebar />
      <main style={{ flex: 1, overflowY: 'auto', minWidth: 0 }}>
        {children}
      </main>
    </div>
  )
}
