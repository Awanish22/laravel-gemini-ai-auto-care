<template>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <h1>🚗 AI AutoCare Assistant</h1>
                    <p>AI-powered vehicle diagnostic system</p>
                </div>
                <div class="nav-links">
                    <button @click="activeTab = 'issues'" :class="{ active: activeTab === 'issues' }">
                        Vehicle Issues
                    </button>
                    <button @click="activeTab = 'report'" :class="{ active: activeTab === 'report' }">
                        Report New Issue
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Vehicle Issues List -->
            <div v-if="activeTab === 'issues'" class="tab-content">
                <VehicleIssuesList 
                    :issues="issues"
                    :loading="loading"
                    :error="error"
                    @view-issue="viewIssueDetail"
                    @request-analysis="requestAIAnalysis"
                    @create-issue="activeTab = 'report'"
                />
            </div>

            <!-- Report New Issue -->
            <div v-if="activeTab === 'report'" class="tab-content">
                <CreateIssueForm 
                    @issue-created="handleIssueCreated"
                    @cancel="activeTab = 'issues'"
                />
            </div>

            <!-- Issue Detail View -->
            <div v-if="activeTab === 'detail' && currentIssue" class="tab-content">
                <VehicleIssueDetail 
                    :issue="currentIssue"
                    @back="activeTab = 'issues'"
                    @update-status="updateIssueStatus"
                    @delete-issue="deleteIssue"
                    @request-analysis="requestAIAnalysis"
                />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import VehicleIssuesList from './components/VehicleIssuesList.vue'
import CreateIssueForm from './components/CreateIssueForm.vue'
import VehicleIssueDetail from './components/VehicleIssueDetail.vue'

// Setup axios instance
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
})

// State
const activeTab = ref('issues')
const issues = ref([])
const currentIssue = ref(null)
const loading = ref(false)
const error = ref(null)

// Computed
const issuesCount = computed(() => issues.value.length)
const pendingIssues = computed(() => issues.value.filter(issue => issue.status === 'pending').length)

// Methods
async function fetchIssues() {
    loading.value = true
    error.value = null
    try {
        const response = await api.get('/vehicle-issues')
        issues.value = response.data.data
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to fetch issues'
        console.error('Fetch issues error:', err)
    } finally {
        loading.value = false
    }
}

async function viewIssueDetail(issueId) {
    loading.value = true
    try {
        const response = await api.get(`/vehicle-issues/${issueId}`)
        currentIssue.value = response.data
        activeTab.value = 'detail'
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to fetch issue details'
    } finally {
        loading.value = false
    }
}

async function requestAIAnalysis(issueId) {
    loading.value = true
    try {
        await api.post(`/vehicle-issues/${issueId}/analyze`)
        
        // Refresh the issue
        if (currentIssue.value && currentIssue.value.id === issueId) {
            const response = await api.get(`/vehicle-issues/${issueId}`)
            currentIssue.value = response.data
        }
        
        // Refresh issues list
        await fetchIssues()
        
        alert('AI analysis completed successfully!')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to get AI analysis'
        alert('Failed to get AI analysis. Please try again.')
    } finally {
        loading.value = false
    }
}

async function handleIssueCreated(newIssue) {
    issues.value.unshift(newIssue)
    activeTab.value = 'issues'
    await fetchIssues() // Refresh list
}

async function updateIssueStatus({ issueId, status }) {
    try {
        await api.put(`/vehicle-issues/${issueId}`, { status })
        
        // Update in issues list
        const index = issues.value.findIndex(issue => issue.id === issueId)
        if (index !== -1) {
            issues.value[index].status = status
        }
        
        // Update current issue if active
        if (currentIssue.value && currentIssue.value.id === issueId) {
            currentIssue.value.status = status
        }
        
        alert('Status updated successfully!')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update status'
    }
}

async function deleteIssue(issueId) {
    if (!confirm('Are you sure you want to delete this issue?')) return
    
    try {
        await api.delete(`/vehicle-issues/${issueId}`)
        
        // Remove from issues list
        issues.value = issues.value.filter(issue => issue.id !== issueId)
        
        // If viewing this issue, go back to list
        if (currentIssue.value && currentIssue.value.id === issueId) {
            activeTab.value = 'issues'
            currentIssue.value = null
        }
        
        alert('Issue deleted successfully!')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete issue'
    }
}

// Lifecycle
onMounted(() => {
    fetchIssues()
})
</script>

<style scoped>
.container {
    min-height: 100vh;
    background-color: #f5f7fa;
}

.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand h1 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.nav-brand p {
    margin: 0.25rem 0 0 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.nav-links {
    display: flex;
    gap: 1rem;
}

.nav-links button {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-links button:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.nav-links button.active {
    background: white;
    color: #764ba2;
}

.main-content {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 2rem;
}

.tab-content {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .nav-container {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .nav-links {
        width: 100%;
        justify-content: center;
    }
}
</style>