// import { defineStore } from "pinia";

// export const useDashboardStore = defineStore("dashboard", () => {
//     const stats = ref(null)
//     const loading = ref(false)
//     const error = ref(null)

//     const fetchStats = async () => {
//         loading.value = true
//         error.value = null
//         try{
//             const response = await  api.get('/dasboard')
//             stats.value = response.data
//             return true
//         } catch (err) {
//             error.value = err.response?.data?.message || 'Erreur lors du chargement du dashboard'
//           return false
//         } finally {
//             loading.value = false
//         }
// }
// return{
//     stats,
//     loading,
//     error,
//     fetchStats

// }
// })