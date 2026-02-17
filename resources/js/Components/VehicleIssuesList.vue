<template>
    <div class="vehicle-issues-list">
        <div class="list-header">
            <h2>Reported Vehicle Issues</h2>
            <div class="stats">
                <span class="stat-item">
                    Total Issues: {{ issues.length }}
                </span>
                <span v-if="pendingIssues > 0" class="stat-item pending">
                    Pending: {{ pendingIssues }}
                </span>
            </div>
        </div>

        <div v-if="loading && issues.length === 0" class="loading-state">
            <div class="spinner"></div>
            <p>Loading issues...</p>
        </div>

        <div v-if="error" class="error-state">
            <div class="error-icon">⚠️</div>
            <p>{{ error }}</p>
            <button @click="$emit('refresh')" class="btn-retry">Try Again</button>
        </div>

        <div v-if="!loading && issues.length === 0" class="empty-state">
            <div class="empty-icon">🚗</div>
            <h3>No issues reported yet</h3>
            <p>Be the first to report a vehicle issue</p>
            <button @click="$emit('create-issue')" class="btn-primary">
                Report First Issue
            </button>
        </div>

        <div v-else class="issues-grid">
            <div 
                v-for="issue in issues" 
                :key="issue.id" 
                class="issue-card"
                :class="issue.status"
            >
                <div class="card-header">
                    <h3>{{ issue.title }}</h3>
                    <span :class="`status-badge ${issue.status}`">
                        {{ issue.status }}
                    </span>
                </div>

                <div class="vehicle-info">
                    <span class="vehicle-make-model">
                        {{ issue.vehicle_year }} {{ issue.vehicle_make }} {{ issue.vehicle_model }}
                    </span>
                    <span v-if="issue.odometer_reading" class="odometer">
                        📍 {{ issue.odometer_reading }}
                    </span>
                </div>

                <p class="issue-description">
                    {{ truncateText(issue.description, 120) }}
                </p>

                <div v-if="issue.ai_analysis" class="ai-preview">
                    <div class="ai-header">
                        <span class="ai-icon">🤖</span>
                        <span class="severity" :class="issue.severity_level?.toLowerCase()">
                            {{ issue.severity_level || 'Analyzed' }}
                        </span>
                    </div>
                    <p class="ai-text">
                        {{ truncateText(issue.ai_analysis, 100) }}
                    </p>
                </div>

                <div class="card-actions">
                    <button @click="$emit('view-issue', issue.id)" class="btn-view">
                        View Details
                    </button>
                    <button 
                        v-if="!issue.ai_analysis" 
                        @click="$emit('request-analysis', issue.id)"
                        class="btn-ai"
                        :disabled="loading"
                    >
                        Get AI Analysis
                    </button>
                    <div v-else class="ai-indicator">
                        <span class="ai-check">✅</span>
                        AI Analyzed
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    issues: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: null
    }
})

const emit = defineEmits(['view-issue', 'request-analysis', 'create-issue', 'refresh'])

const pendingIssues = computed(() => 
    props.issues.filter(issue => issue.status === 'pending').length
)

function truncateText(text, length) {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}
</script>

<style scoped>
.vehicle-issues-list {
    padding: 20px 0;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.list-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.8rem;
}

.stats {
    display: flex;
    gap: 20px;
    align-items: center;
}

.stat-item {
    padding: 8px 16px;
    background: #f0f7ff;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #2196F3;
}

.stat-item.pending {
    background: #fff3cd;
    color: #856404;
}

.loading-state, .empty-state, .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.empty-icon, .error-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.btn-primary, .btn-retry {
    margin-top: 20px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-retry {
    background: #6c757d;
}

.btn-primary:hover, .btn-retry:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.issues-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.issue-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.issue-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.issue-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}

.issue-card.pending::before {
    background: #ff9800;
}

.issue-card.analyzed::before {
    background: #2196F3;
}

.issue-card.in_progress::before {
    background: #9c27b0;
}

.issue-card.resolved::before {
    background: #4CAF50;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.card-header h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
    flex: 1;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.analyzed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-badge.in_progress {
    background: #e2d9f3;
    color: #6f42c1;
}

.status-badge.resolved {
    background: #d4edda;
    color: #155724;
}

.vehicle-info {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    color: #666;
}

.vehicle-make-model {
    background: #f8f9fa;
    padding: 4px 12px;
    border-radius: 4px;
}

.odometer {
    background: #e7f5ff;
    padding: 4px 12px;
    border-radius: 4px;
    color: #0d6efd;
}

.issue-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.ai-preview {
    background: #f0f7ff;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    border-left: 3px solid #2196F3;
}

.ai-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.ai-icon {
    font-size: 1.2rem;
}

.severity {
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.severity.low {
    background: #d4edda;
    color: #155724;
}

.severity.medium {
    background: #fff3cd;
    color: #856404;
}

.severity.high {
    background: #f8d7da;
    color: #721c24;
}

.severity.critical {
    background: #721c24;
    color: white;
}

.ai-text {
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0;
}

.card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    gap: 10px;
}

.btn-view, .btn-ai {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.btn-view {
    background: #6c757d;
    color: white;
}

.btn-view:hover {
    background: #5a6268;
}

.btn-ai {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-ai:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-ai:hover:not(:disabled) {
    opacity: 0.9;
    transform: translateY(-2px);
}

.ai-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #28a745;
    font-weight: 500;
}

.ai-check {
    font-size: 1rem;
}

@media (max-width: 768px) {
    .issues-grid {
        grid-template-columns: 1fr;
    }
    
    .list-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>