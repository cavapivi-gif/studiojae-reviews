import { Routes, Route, Navigate } from 'react-router-dom'
import Layout        from './components/Layout'
import ErrorBoundary from './components/ErrorBoundary'
import Dashboard     from './pages/Dashboard'
import Reviews       from './pages/Reviews'
import ReviewForm    from './pages/ReviewForm'
import Settings      from './pages/Settings'
import Lieux         from './pages/Lieux'
import Docs          from './pages/Docs'

export default function App() {
  return (
    <ErrorBoundary>
      <Layout>
        <Routes>
          <Route path="/"              element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard"     element={<Dashboard />} />
          <Route path="/reviews"       element={<Reviews />} />
          <Route path="/reviews/new"   element={<ReviewForm />} />
          <Route path="/reviews/:id"   element={<ReviewForm />} />
          <Route path="/lieux"         element={<Lieux />} />
          <Route path="/settings"      element={<Settings />} />
          <Route path="/docs"          element={<Docs />} />
        </Routes>
      </Layout>
    </ErrorBoundary>
  )
}
