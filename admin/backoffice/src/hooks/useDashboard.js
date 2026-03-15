import { useState, useEffect, useMemo, useCallback, useRef } from 'react'
import { api } from '../lib/api'
import { useToast } from '../components/Toast'

/** Calcule la période précédente équivalente pour le comparateur de tendances */
function getPreviousPeriodDates(period, fromDate, toDate) {
  const now = new Date()
  const fmt = d => d.toISOString().slice(0, 10)

  if (period === '7d') {
    const end = new Date(now); end.setDate(end.getDate() - 7)
    const start = new Date(now); start.setDate(start.getDate() - 14)
    return { from: fmt(start), to: fmt(end) }
  }
  if (period === '30d') {
    const end = new Date(now); end.setDate(end.getDate() - 30)
    const start = new Date(now); start.setDate(start.getDate() - 60)
    return { from: fmt(start), to: fmt(end) }
  }
  if (period === '90d') {
    const end = new Date(now); end.setDate(end.getDate() - 90)
    const start = new Date(now); start.setDate(start.getDate() - 180)
    return { from: fmt(start), to: fmt(end) }
  }
  if (period === '12m') {
    const end = new Date(now); end.setFullYear(end.getFullYear() - 1)
    const start = new Date(now); start.setFullYear(start.getFullYear() - 2)
    return { from: fmt(start), to: fmt(end) }
  }
  if (period === 'custom' && fromDate && toDate) {
    const from = new Date(fromDate)
    const to = new Date(toDate)
    const duration = to - from
    const prevTo = new Date(from - 1)
    const prevFrom = new Date(prevTo - duration)
    return { from: fmt(prevFrom), to: fmt(prevTo) }
  }
  return null // 'all' → pas de comparateur
}

export function useDashboard() {
  const toast = useToast()
  const [data, setData] = useState(null)
  const [lieux, setLieux] = useState([])
  const [loading, setLoading] = useState(true)
  const [period, setPeriod] = useState('all')
  const [sourceFilter, setSourceFilter] = useState('')
  const [lieuFilter, setLieuFilter] = useState('')
  const [granularity, setGranularity] = useState('')

  // Custom date range
  const [fromDate, setFromDate] = useState('')
  const [toDate, setToDate] = useState('')

  // Trends (separate endpoint)
  const [trends, setTrends] = useState(null)
  const [trendsLoading, setTrendsLoading] = useState(false)

  // Comparateur de tendances "vs période précédente"
  const [compareEnabled, setCompareEnabled] = useState(false)
  const [prevTrends, setPrevTrends] = useState(null)
  const [prevTrendsLoading, setPrevTrendsLoading] = useState(false)

  // Season comparison (section séparée)
  const [comparison, setComparison] = useState(null)
  const [comparisonLoading, setComparisonLoading] = useState(false)

  // Request IDs (stale-response guard)
  const dashReqId     = useRef(0)
  const trendReqId    = useRef(0)
  const prevTrendReqId = useRef(0)

  // Fetch lieux ONCE
  useEffect(() => {
    api.lieux().then(setLieux).catch(() => {})
  }, [])

  // Fetch dashboard on filter change
  useEffect(() => {
    const id = ++dashReqId.current
    setLoading(true)
    api.dashboard(period, sourceFilter, lieuFilter, fromDate, toDate)
      .then(d => { if (dashReqId.current === id) setData(d) })
      .catch(e => { if (dashReqId.current === id) toast.error(e.message) })
      .finally(() => { if (dashReqId.current === id) setLoading(false) })
  }, [period, sourceFilter, lieuFilter, fromDate, toDate])

  // Fetch trends on filter change
  useEffect(() => {
    const id = ++trendReqId.current
    setTrendsLoading(true)
    api.dashboardTrends(period, sourceFilter, lieuFilter, fromDate, toDate, granularity)
      .then(d => { if (trendReqId.current === id) setTrends(d) })
      .catch(() => { if (trendReqId.current === id) setTrends(null) })
      .finally(() => { if (trendReqId.current === id) setTrendsLoading(false) })
  }, [period, sourceFilter, lieuFilter, fromDate, toDate, granularity])

  // Fetch previous period trends when comparateur is enabled
  useEffect(() => {
    if (!compareEnabled) {
      setPrevTrends(null)
      return
    }
    const prev = getPreviousPeriodDates(period, fromDate, toDate)
    if (!prev) {
      setPrevTrends(null)
      return
    }
    const id = ++prevTrendReqId.current
    setPrevTrendsLoading(true)
    api.dashboardTrends('custom', sourceFilter, lieuFilter, prev.from, prev.to, granularity)
      .then(d => { if (prevTrendReqId.current === id) setPrevTrends(d) })
      .catch(() => { if (prevTrendReqId.current === id) setPrevTrends(null) })
      .finally(() => { if (prevTrendReqId.current === id) setPrevTrendsLoading(false) })
  }, [compareEnabled, period, sourceFilter, lieuFilter, fromDate, toDate, granularity])

  // Monthly trend (MiniBarChart)
  const monthlyTrend = useMemo(() => {
    if (!data?.monthly_trend?.length) return []
    return data.monthly_trend.map(m => ({
      label: new Date(m.year, m.month - 1).toLocaleDateString('fr-FR', { month: 'short', year: m.year !== new Date().getFullYear() ? '2-digit' : undefined }),
      count: m.count,
    }))
  }, [data])

  // Active lieux
  const activeLieux = useMemo(() => lieux.filter(l => l.active), [lieux])

  // Compare seasons or custom ranges (section séparée SeasonCompare)
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

  // Flush cache + reload all
  const reload = useCallback(async () => {
    await api.flushCache()
    const id = ++dashReqId.current
    setLoading(true)
    api.dashboard(period, sourceFilter, lieuFilter, fromDate, toDate)
      .then(d => { if (dashReqId.current === id) setData(d) })
      .catch(e => { if (dashReqId.current === id) toast.error(e.message) })
      .finally(() => { if (dashReqId.current === id) setLoading(false) })
    const tid = ++trendReqId.current
    setTrendsLoading(true)
    api.dashboardTrends(period, sourceFilter, lieuFilter, fromDate, toDate, granularity)
      .then(d => { if (trendReqId.current === tid) setTrends(d) })
      .catch(() => { if (trendReqId.current === tid) setTrends(null) })
      .finally(() => { if (trendReqId.current === tid) setTrendsLoading(false) })
  }, [period, sourceFilter, lieuFilter, fromDate, toDate, granularity])

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
    granularity, setGranularity,
    fromDate, setFromDate, toDate, setToDate,
    lieux, activeLieux, monthlyTrend,
    trends, trendsLoading,
    compareEnabled, setCompareEnabled, prevTrends, prevTrendsLoading,
    comparison, comparisonLoading, compareSeason, compareRange,
    reload,
  }
}
