import { useState, useEffect, useMemo, useCallback } from 'react'
import { api } from '../lib/api'
import { useToast } from '../components/Toast'

export function useDashboard() {
  const toast = useToast()
  const [data, setData] = useState(null)
  const [lieux, setLieux] = useState([])
  const [loading, setLoading] = useState(true)
  const [period, setPeriod] = useState('all')
  const [sourceFilter, setSourceFilter] = useState('')
  const [lieuFilter, setLieuFilter] = useState('')

  // Trends (separate endpoint)
  const [trends, setTrends] = useState(null)
  const [trendsLoading, setTrendsLoading] = useState(false)

  // Season comparison
  const [comparison, setComparison] = useState(null)
  const [comparisonLoading, setComparisonLoading] = useState(false)

  // Fetch lieux ONCE
  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  // Fetch dashboard on filter change
  useEffect(() => {
    setLoading(true)
    api.dashboard(period, sourceFilter, lieuFilter)
      .then(setData)
      .catch(e => toast.error(e.message))
      .finally(() => setLoading(false))
  }, [period, sourceFilter, lieuFilter])

  // Fetch trends on filter change
  useEffect(() => {
    setTrendsLoading(true)
    api.dashboardTrends(period, sourceFilter, lieuFilter)
      .then(setTrends)
      .catch(() => setTrends(null))
      .finally(() => setTrendsLoading(false))
  }, [period, sourceFilter, lieuFilter])

  // Monthly trend derived from main data (for MiniBarChart)
  const monthlyTrend = useMemo(() => {
    if (!data?.monthly_trend?.length) return []
    return data.monthly_trend.map(m => ({
      label: new Date(m.year, m.month - 1).toLocaleDateString('fr-FR', { month: 'short' }),
      count: m.count,
    }))
  }, [data])

  // Active lieux (memoized)
  const activeLieux = useMemo(() => lieux.filter(l => l.active), [lieux])

  // Compare seasons
  const compareSeason = useCallback(async (season1, year1, season2, year2) => {
    setComparisonLoading(true)
    try {
      const result = await api.dashboardCompare(season1, year1, season2, year2)
      setComparison(result)
    } catch (e) {
      toast.error(e.message)
      setComparison(null)
    } finally {
      setComparisonLoading(false)
    }
  }, [])

  return {
    data, loading, period, setPeriod,
    sourceFilter, setSourceFilter, lieuFilter, setLieuFilter,
    lieux, activeLieux, monthlyTrend,
    trends, trendsLoading,
    comparison, comparisonLoading, compareSeason,
  }
}
