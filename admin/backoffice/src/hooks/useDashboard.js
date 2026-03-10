import { useState, useEffect, useMemo, useCallback, useRef } from 'react'
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

  // Custom date range
  const [fromDate, setFromDate] = useState('')
  const [toDate, setToDate] = useState('')

  // Trends (separate endpoint)
  const [trends, setTrends] = useState(null)
  const [trendsLoading, setTrendsLoading] = useState(false)

  // Season comparison
  const [comparison, setComparison] = useState(null)
  const [comparisonLoading, setComparisonLoading] = useState(false)

  // Request ID to prevent stale responses from overwriting newer data
  const dashReqId = useRef(0)
  const trendReqId = useRef(0)

  // Fetch lieux ONCE
  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  // Fetch dashboard on filter change (with stale-request guard)
  useEffect(() => {
    const id = ++dashReqId.current
    setLoading(true)
    api.dashboard(period, sourceFilter, lieuFilter, fromDate, toDate)
      .then(d => { if (dashReqId.current === id) setData(d) })
      .catch(e => { if (dashReqId.current === id) toast.error(e.message) })
      .finally(() => { if (dashReqId.current === id) setLoading(false) })
  }, [period, sourceFilter, lieuFilter, fromDate, toDate])

  // Fetch trends on filter change (with stale-request guard)
  useEffect(() => {
    const id = ++trendReqId.current
    setTrendsLoading(true)
    api.dashboardTrends(period, sourceFilter, lieuFilter, fromDate, toDate)
      .then(d => { if (trendReqId.current === id) setTrends(d) })
      .catch(() => { if (trendReqId.current === id) setTrends(null) })
      .finally(() => { if (trendReqId.current === id) setTrendsLoading(false) })
  }, [period, sourceFilter, lieuFilter, fromDate, toDate])

  // Monthly trend derived from main data (for MiniBarChart)
  const monthlyTrend = useMemo(() => {
    if (!data?.monthly_trend?.length) return []
    return data.monthly_trend.map(m => ({
      label: new Date(m.year, m.month - 1).toLocaleDateString('fr-FR', { month: 'short', year: m.year !== new Date().getFullYear() ? '2-digit' : undefined }),
      count: m.count,
    }))
  }, [data])

  // Active lieux (memoized)
  const activeLieux = useMemo(() => lieux.filter(l => l.active), [lieux])

  // Compare seasons or custom ranges
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

  // Compare custom date ranges
  const compareRange = useCallback(async (from1, to1, from2, to2) => {
    setComparisonLoading(true)
    try {
      const result = await api.dashboardCompareRange(from1, to1, from2, to2)
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
    fromDate, setFromDate, toDate, setToDate,
    lieux, activeLieux, monthlyTrend,
    trends, trendsLoading,
    comparison, comparisonLoading, compareSeason, compareRange,
  }
}
