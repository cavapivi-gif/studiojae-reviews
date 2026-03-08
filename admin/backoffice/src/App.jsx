import { Routes, Route, Navigate } from 'react-router-dom'
import Layout    from './components/Layout'
import Dashboard from './pages/Dashboard'
import Reviews   from './pages/Reviews'
import ReviewForm from './pages/ReviewForm'
import Settings  from './pages/Settings'

export default function App() {
  return (
    <Layout>
      <Routes>
        <Route path="/"           element={<Navigate to="/dashboard" replace />} />
        <Route path="/dashboard"  element={<Dashboard />} />
        <Route path="/reviews"    element={<Reviews />} />
        <Route path="/reviews/new"    element={<ReviewForm />} />
        <Route path="/reviews/:id"    element={<ReviewForm />} />
        <Route path="/settings"   element={<Settings />} />
      </Routes>
    </Layout>
  )
}
