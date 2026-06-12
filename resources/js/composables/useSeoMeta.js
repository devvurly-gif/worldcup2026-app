/**
 * Dynamic SEO meta tags per page.
 * Updates <title>, meta description, og:title, og:description, og:url, twitter:title
 */
const BASE    = 'Mondial 2026 Scores'
const BASE_DESC = 'Coupe du Monde FIFA 2026 – Scores, groupes, calendrier, joueurs et actualités.'
const BASE_URL  = 'https://mondial26score.com'

export function useSeoMeta({ title, description, path } = {}) {
  const fullTitle = title ? `${title} | ${BASE}` : BASE
  const desc      = description ?? BASE_DESC
  const url       = path ? `${BASE_URL}/#${path}` : BASE_URL

  // Title
  document.title = fullTitle

  // Meta description
  setMeta('name', 'description', desc)

  // OG
  setMeta('property', 'og:title',       fullTitle)
  setMeta('property', 'og:description', desc)
  setMeta('property', 'og:url',         url)

  // Twitter
  setMeta('name', 'twitter:title',       fullTitle)
  setMeta('name', 'twitter:description', desc)

  // Canonical
  let canonical = document.querySelector('link[rel="canonical"]')
  if (!canonical) {
    canonical = document.createElement('link')
    canonical.rel = 'canonical'
    document.head.appendChild(canonical)
  }
  canonical.href = url
}

function setMeta(attr, key, value) {
  let el = document.querySelector(`meta[${attr}="${key}"]`)
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute(attr, key)
    document.head.appendChild(el)
  }
  el.setAttribute('content', value)
}
