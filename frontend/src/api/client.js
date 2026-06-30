import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const BASE_URL = API_URL.replace('/api', '') // http://localhost:8000

const client = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Essentiel pour Sanctum (cookies)
  withXSRFToken: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

export default client
export { BASE_URL }
