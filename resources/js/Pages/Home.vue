<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo & Brand -->
                    <div class="flex items-center space-x-3 group">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform duration-300">
                                <span class="text-2xl filter drop-shadow-lg">🔧</span>
                            </div>
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                AI AutoCare
                            </h1>
                            <p class="text-xs text-slate-500">Intelligent Vehicle Diagnostics</p>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="flex space-x-2 bg-slate-100/80 p-1.5 rounded-2xl">
                        <button 
                            @click="activeTab = 'issues'"
                            :class="[
                                'px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-200',
                                activeTab === 'issues' 
                                    ? 'bg-white text-blue-600 shadow-md shadow-blue-500/10 ring-1 ring-blue-100' 
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'
                            ]"
                        >
                            <span class="flex items-center space-x-2">
                                <span class="text-lg">📋</span>
                                <span>Issues Board</span>
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'report'"
                            :class="[
                                'px-6 py-2.5 rounded-xl font-medium text-sm transition-all duration-200',
                                activeTab === 'report' 
                                    ? 'bg-white text-indigo-600 shadow-md shadow-indigo-500/10 ring-1 ring-indigo-100' 
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'
                            ]"
                        >
                            <span class="flex items-center space-x-2">
                                <span class="text-lg">➕</span>
                                <span>New Report</span>
                            </span>
                        </button>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <button class="relative group">
                            <div class="flex items-center space-x-3 bg-slate-100/80 hover:bg-slate-200/80 rounded-2xl px-4 py-2 transition-all duration-200">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white font-semibold shadow-sm">
                                    JD
                                </div>
                                <div class="text-left hidden md:block">
                                    <p class="text-sm font-semibold text-slate-700">John Doe</p>
                                    <p class="text-xs text-slate-500">Master Technician</p>
                                </div>
                                <span class="text-slate-400 group-hover:text-slate-600 transition-colors text-xs">▼</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Vehicle Issues Board -->
            <div v-if="activeTab === 'issues'" class="space-y-6">
                <!-- Header Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                    <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:border-blue-100 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 mb-1">Total Issues</p>
                                <p class="text-3xl font-bold text-slate-800 group-hover:text-blue-600 transition-colors">{{ issues.length }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl">📊</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            <span class="text-blue-600 font-medium">+{{ issues.length }}</span> this month
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:border-amber-100 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 mb-1">Pending Review</p>
                                <p class="text-3xl font-bold text-amber-600">{{ pendingIssues }}</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl">⏳</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            <span class="text-amber-600 font-medium">{{ ((pendingIssues / issues.length) * 100 || 0).toFixed(0) }}%</span> of total
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:border-purple-100 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 mb-1">AI Analyzed</p>
                                <p class="text-3xl font-bold text-purple-600">{{ issues.filter(i => i.ai_analysis).length }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl">🤖</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            <span class="text-purple-600 font-medium">{{ ((issues.filter(i => i.ai_analysis).length / issues.length) * 100 || 0).toFixed(0) }}%</span> processed
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 mb-1">Resolved</p>
                                <p class="text-3xl font-bold text-emerald-600">{{ issues.filter(i => i.status === 'resolved').length }}</p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="text-2xl">✅</span>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">
                            <span class="text-emerald-600 font-medium">{{ ((issues.filter(i => i.status === 'resolved').length / issues.length) * 100 || 0).toFixed(0) }}%</span> completion rate
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <!-- Board Header -->
                    <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <h2 class="text-xl font-semibold text-slate-800 flex items-center">
                                <span class="w-1.5 h-6 bg-gradient-to-b from-blue-500 to-indigo-500 rounded-full mr-3"></span>
                                Issues Management Board
                            </h2>
                            
                            <!-- Filters -->
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <select class="appearance-none px-5 py-2.5 bg-slate-100 border-0 rounded-xl text-sm text-slate-600 focus:ring-2 focus:ring-blue-500/20 pr-10 cursor-pointer hover:bg-slate-200 transition-colors">
                                        <option>All Status</option>
                                        <option>Pending</option>
                                        <option>Analyzed</option>
                                        <option>In Progress</option>
                                        <option>Resolved</option>
                                    </select>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">▼</span>
                                </div>
                                <div class="relative">
                                    <select class="appearance-none px-5 py-2.5 bg-slate-100 border-0 rounded-xl text-sm text-slate-600 focus:ring-2 focus:ring-blue-500/20 pr-10 cursor-pointer hover:bg-slate-200 transition-colors">
                                        <option>All Severity</option>
                                        <option>Low</option>
                                        <option>Medium</option>
                                        <option>High</option>
                                        <option>Critical</option>
                                    </select>
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">▼</span>
                                </div>
                                <button class="p-2.5 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors group">
                                    <span class="text-slate-600 group-hover:text-blue-600 transition-colors">🔍</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading && issues.length === 0" class="text-center py-20">
                        <div class="relative inline-block">
                            <div class="w-20 h-20 border-4 border-slate-200 rounded-full"></div>
                            <div class="absolute top-0 left-0 w-20 h-20 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
                        </div>
                        <p class="mt-6 text-slate-500 font-medium">Loading your diagnostic data...</p>
                        <p class="text-sm text-slate-400 mt-2">Please wait while we fetch the latest issues</p>
                    </div>

                    <!-- Error State -->
                    <div v-if="error" class="p-8">
                        <div class="bg-rose-50 rounded-2xl p-8 text-center max-w-md mx-auto">
                            <div class="w-20 h-20 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-4xl">⚠️</span>
                            </div>
                            <h3 class="text-lg font-semibold text-rose-800 mb-2">Connection Error</h3>
                            <p class="text-rose-600 mb-6">{{ error }}</p>
                            <button 
                                @click="fetchIssues"
                                class="px-6 py-3 bg-rose-600 text-white rounded-xl hover:bg-rose-700 transition-colors shadow-lg shadow-rose-500/25 font-medium"
                            >
                                Try Again
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!loading && issues.length === 0" class="text-center py-20 px-6">
                        <div class="max-w-md mx-auto">
                            <div class="relative mb-8">
                                <div class="text-8xl mb-4 animate-bounce">🚗</div>
                                <div class="absolute -top-2 -right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-pulse">
                                    +1
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-800 mb-3">No issues reported yet</h3>
                            <p class="text-slate-500 mb-8">Start by reporting your first vehicle issue to get AI-powered diagnostics and instant analysis</p>
                            <button 
                                @click="activeTab = 'report'"
                                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:shadow-xl hover:shadow-blue-500/25 transition-all hover:scale-105"
                            >
                                <span class="mr-2 text-lg">➕</span>
                                Report First Issue
                            </button>
                        </div>
                    </div>

                    <!-- Issues Grid - Enhanced Cards -->
                    <div v-else class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div 
                                v-for="issue in issues" 
                                :key="issue.id"
                                class="group relative bg-white rounded-2xl border border-slate-200 hover:border-transparent hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-300 overflow-hidden"
                            >
                                <!-- Status Ribbon -->
                                <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden z-10">
                                    <div :class="[
                                        'absolute top-5 right-[-35px] rotate-45 w-36 py-1.5 text-center text-xs font-bold text-white shadow-lg',
                                        statusRibbonClasses[issue.status] || 'bg-slate-500'
                                    ]">
                                        {{ issue.status }}
                                    </div>
                                </div>

                                <!-- Card Header with Gradient -->
                                <div class="relative h-40 bg-gradient-to-br from-slate-900 to-slate-800 p-5 overflow-hidden">
                                    <!-- Decorative Pattern -->
                                    <div class="absolute inset-0 opacity-10">
                                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white rounded-full"></div>
                                        <div class="absolute -left-4 -bottom-4 w-32 h-32 bg-white rounded-full"></div>
                                    </div>
                                    
                                    <div class="relative flex justify-between items-start">
                                        <div>
                                            <span 
                                                :class="[
                                                    'px-3 py-1.5 rounded-full text-xs font-bold shadow-lg backdrop-blur-sm',
                                                    severityClasses[issue.severity_level?.toLowerCase()] || 'bg-slate-500/90 text-white'
                                                ]"
                                            >
                                                {{ issue.severity_level || 'Pending Analysis' }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse delay-75"></span>
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse delay-150"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Vehicle Info Overlay -->
                                    <div class="absolute bottom-4 left-5 right-5">
                                        <h3 class="font-bold text-lg text-white truncate drop-shadow-md">{{ issue.title }}</h3>
                                        <p class="text-sm text-slate-300 flex items-center">
                                            <span class="mr-1">🚘</span>
                                            {{ issue.vehicle_year }} {{ issue.vehicle_make }} {{ issue.vehicle_model }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-5">
                                    <!-- Odometer Badge -->
                                    <div v-if="issue.odometer_reading" class="flex items-center mb-4 text-sm bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">
                                        <span class="mr-2 text-slate-400">📍</span>
                                        <span class="font-medium text-slate-700">{{ issue.odometer_reading }}</span>
                                        <span class="ml-auto text-xs text-slate-400">odometer</span>
                                    </div>

                                    <!-- Description -->
                                    <p class="text-slate-600 text-sm mb-4 line-clamp-2 leading-relaxed">
                                        {{ truncateText(issue.description, 100) }}
                                    </p>

                                    <!-- AI Analysis Preview -->
                                    <div v-if="issue.ai_analysis" class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 mb-4 border border-blue-100">
                                        <div class="flex items-center mb-2">
                                            <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                                                <span class="text-sm">🤖</span>
                                            </div>
                                            <span class="text-xs font-semibold text-blue-600">AI DIAGNOSIS</span>
                                            <span class="ml-auto text-xs text-blue-400">confidence 98%</span>
                                        </div>
                                        <p class="text-slate-700 text-sm line-clamp-2">
                                            {{ truncateText(issue.ai_analysis, 80) }}
                                        </p>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="flex items-center space-x-3 mb-4 text-xs text-slate-400">
                                        <span class="flex items-center">
                                            <span class="mr-1">🕒</span>
                                            {{ new Date().toLocaleDateString() }}
                                        </span>
                                        <span class="flex items-center">
                                            <span class="mr-1">👤</span>
                                            John Doe
                                        </span>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-100">
                                        <button 
                                            @click="viewIssueDetail(issue)"
                                            class="px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition-colors flex items-center space-x-2 group-hover:bg-blue-600 group-hover:shadow-lg group-hover:shadow-blue-500/25"
                                        >
                                            <span>View Details</span>
                                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                                        </button>
                                        
                                        <div v-if="!issue.ai_analysis" class="relative group/analysis">
                                            <button 
                                                @click="requestAIAnalysis(issue.id)"
                                                :disabled="loading"
                                                class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transition-all disabled:opacity-50 flex items-center space-x-2"
                                            >
                                                <span class="animate-pulse">🤖</span>
                                                <span>Analyze</span>
                                            </button>
                                            <!-- Tooltip -->
                                            <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 hidden group-hover/analysis:block z-20">
                                                <div class="bg-slate-900 text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap shadow-lg">
                                                    Run AI diagnostics
                                                    <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-else class="flex items-center text-emerald-600 text-sm bg-emerald-50 px-3 py-1.5 rounded-xl">
                                            <span class="mr-1">✅</span>
                                            <span class="font-medium">AI Ready</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hover Effect Overlay -->
                                <div class="absolute inset-0 border-2 border-transparent group-hover:border-blue-500/20 rounded-2xl pointer-events-none transition-all duration-300"></div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-6">
                            <p class="text-sm text-slate-500">
                                Showing <span class="font-medium">{{ issues.length }}</span> of <span class="font-medium">{{ issues.length }}</span> results
                            </p>
                            <div class="flex space-x-2">
                                <button class="px-4 py-2 bg-slate-100 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors disabled:opacity-50" disabled>
                                    ← Previous
                                </button>
                                <button class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                                    1
                                </button>
                                <button class="px-4 py-2 bg-slate-100 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors">
                                    2
                                </button>
                                <button class="px-4 py-2 bg-slate-100 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors">
                                    3
                                </button>
                                <button class="px-4 py-2 bg-slate-100 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors">
                                    Next →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report New Issue -->
            <div v-if="activeTab === 'report'" class="max-w-4xl mx-auto">
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                        <h2 class="text-xl font-semibold text-slate-800 flex items-center">
                            <span class="w-1.5 h-6 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full mr-3"></span>
                            Report New Vehicle Issue
                        </h2>
                    </div>
                    <div class="p-6">
                        <CreateIssueForm 
                            @issue-created="handleIssueCreated"
                            @cancel="activeTab = 'issues'"
                        />
                    </div>
                </div>
            </div>

            <!-- Issue Detail View -->
            <div v-if="activeTab === 'detail' && currentIssue" class="max-w-4xl mx-auto">
                <VehicleIssueDetail 
                    :issue="currentIssue"
                    @back="activeTab = 'issues'"
                    @update-status="updateIssueStatus"
                    @delete-issue="deleteIssue"
                    @request-analysis="requestAIAnalysis"
                />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white/80 backdrop-blur-sm border-t border-slate-200/50 mt-16">
            <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl">🤖</span>
                        <p class="text-slate-600 text-sm">
                            AI AutoCare Assistant · Powered by Advanced Vehicle Diagnostics
                        </p>
                    </div>
                    <div class="flex space-x-8 mt-4 md:mt-0">
                        <a href="#" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">About</a>
                        <a href="#" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Privacy</a>
                        <a href="#" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Terms</a>
                        <a href="#" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Contact</a>
                    </div>
                </div>
                <div class="mt-6 text-center text-xs text-slate-400">
                    © 2026 AI AutoCare Assistant. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import CreateIssueForm from '../Components/CreateIssueForm.vue'
import VehicleIssueDetail from '../Components/VehicleIssueDetail.vue'

// State
const activeTab = ref('issues')
const issues = ref([])
const currentIssue = ref(null)
const loading = ref(false)
const error = ref(null)

// Status and severity classes
const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    analyzed: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-purple-100 text-purple-800',
    resolved: 'bg-emerald-100 text-emerald-800'
}

// Ribbon classes for status
const statusRibbonClasses = {
    pending: 'bg-amber-500',
    analyzed: 'bg-blue-500',
    in_progress: 'bg-purple-500',
    resolved: 'bg-emerald-500'
}

const severityClasses = {
    low: 'bg-emerald-500/90 text-white',
    medium: 'bg-amber-500/90 text-white',
    high: 'bg-rose-500/90 text-white',
    critical: 'bg-rose-700/90 text-white'
}

// Computed
const pendingIssues = computed(() => 
    issues.value.filter(issue => issue.status === 'pending').length
)

// Methods
function truncateText(text, length) {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}

// Setup axios
const axiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
})

async function fetchIssues() {
    loading.value = true
    error.value = null
    try {
        const response = await axios.get('/api/vehicle-issues')
        issues.value = response.data.data.data
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to fetch issues'
        console.error('Fetch issues error:', err)
    } finally {
        loading.value = false
    }
}

function viewIssueDetail(issue) {
    currentIssue.value = issue
    activeTab.value = 'detail'
}

async function requestAIAnalysis(issueId) {
    loading.value = true
    try {
        await axios.post(`/api/vehicle-issues/${issueId}/analyze`)
        
        if (currentIssue.value && currentIssue.value.id === issueId) {
            const response = await axios.get(`/api/vehicle-issues/${issueId}`)
            currentIssue.value = response.data
        }
        
        await fetchIssues()
        
        alert('AI analysis completed successfully!')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to get AI analysis'
        alert('Failed to get AI analysis. Please try again.')
    } finally {
        loading.value = false
    }
}

function handleIssueCreated(newIssue) {
    issues.value.unshift(newIssue)
    activeTab.value = 'issues'
    fetchIssues()
}

async function updateIssueStatus({ issueId, status }) {
    try {
        await axios.put(`/api/vehicle-issues/${issueId}`, { status })
        
        const index = issues.value.findIndex(issue => issue.id === issueId)
        if (index !== -1) {
            issues.value[index].status = status
        }
        
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
        await axios.delete(`/api/vehicle-issues/${issueId}`)
        
        issues.value = issues.value.filter(issue => issue.id !== issueId)
        
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-75 {
    animation-delay: 75ms;
}

.delay-150 {
    animation-delay: 150ms;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>