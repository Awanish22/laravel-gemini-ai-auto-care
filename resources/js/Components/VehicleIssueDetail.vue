<template>
    <div class="issue-detail">
        <!-- Back Button -->
        <button @click="$emit('back')" class="back-button">
            <span>←</span> Back to Issues
        </button>

        <div v-if="!issue" class="loading">
            <div class="spinner"></div>
            <p>Loading issue details...</p>
        </div>

        <div v-else class="detail-content">
            <!-- Header Section -->
            <div class="detail-header">
                <div>
                    <h1>{{ issue.title }}</h1>
                    <div class="meta-info">
                        <span class="vehicle-display">
                            🚗 {{ issue.vehicle_year }} {{ issue.vehicle_make }} {{ issue.vehicle_model }}
                        </span>
                        <span v-if="issue.odometer_reading" class="odometer">
                            • 📍 {{ issue.odometer_reading }}
                        </span>
                        <span class="date">
                            • 📅 {{ formatDate(issue.created_at) }}
                        </span>
                    </div>
                </div>
                <div class="header-actions">
                    <span :class="`status-display ${issue.status}`">
                        {{ issue.status }}
                    </span>
                    <button 
                        v-if="!issue.ai_analysis"
                        @click="$emit('request-analysis', issue.id)"
                        class="btn-ai-analyze"
                        :disabled="loading"
                    >
                        🤖 Get AI Analysis
                    </button>
                </div>
            </div>

            <!-- Description Section -->
            <div class="section">
                <h2>Issue Description</h2>
                <div class="description-box">
                    {{ issue.description }}
                </div>
            </div>

            <!-- AI Analysis Section -->
            <div v-if="issue.ai_analysis" class="ai-section">
                <div class="section-header">
                    <h2>🤖 AI Diagnostic Report</h2>
                    <div class="severity-display" :class="issue.severity_level?.toLowerCase()">
                        {{ issue.severity_level }} Severity
                    </div>
                </div>

                <div class="ai-grid">
                    <!-- Analysis -->
                    <div class="ai-card analysis">
                        <h3>📋 Analysis</h3>
                        <div class="card-content">
                            <p>{{ issue.ai_analysis }}</p>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <div class="ai-card recommendations">
                        <h3>✅ Recommendations</h3>
                        <div class="card-content">
                            <ul v-if="issue.ai_recommendations">
                                <li v-for="(recommendation, index) in issue.ai_recommendations" :key="index">
                                    {{ recommendation }}
                                </li>
                            </ul>
                            <p v-else>No specific recommendations available.</p>
                        </div>
                    </div>

                    <!-- Cost & Urgency -->
                    <div class="ai-card metrics">
                        <h3>💰 Cost & Urgency</h3>
                        <div class="card-content">
                            <div class="metric">
                                <span class="metric-label">Estimated Cost:</span>
                                <span class="metric-value">
                                    ${{ issue.estimated_cost ? issue.estimated_cost: 'N/A' }}
                                </span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Urgency:</span>
                                <span class="metric-value urgency" :class="getUrgencyClass(issue.severity_level)">
                                    {{ getUrgencyText(issue.severity_level) }}
                                </span>
                            </div>
                            <div class="metric">
                                <span class="metric-label">Analysis Time:</span>
                                <span class="metric-value">
                                    {{ formatDate(issue.updated_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No AI Analysis State -->
            <div v-else class="no-analysis">
                <div class="no-analysis-content">
                    <div class="ai-icon">🤖</div>
                    <h3>AI Analysis Pending</h3>
                    <p>Click the "Get AI Analysis" button to generate an AI-powered diagnostic report.</p>
                    <button 
                        @click="$emit('request-analysis', issue.id)"
                        class="btn-ai-large"
                        :disabled="loading"
                    >
                        {{ loading ? 'Analyzing...' : 'Generate AI Analysis' }}
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="action-section">
                <h3>Manage Issue</h3>
                <div class="action-buttons">
                    <button 
                        @click="updateStatus('in_progress')"
                        class="btn-action"
                        :class="{ active: issue.status === 'in_progress' }"
                    >
                        🛠️ Mark as In Progress
                    </button>
                    <button 
                        @click="updateStatus('resolved')"
                        class="btn-action resolved"
                        :class="{ active: issue.status === 'resolved' }"
                    >
                        ✅ Mark as Resolved
                    </button>
                    <button 
                        @click="confirmDelete"
                        class="btn-action delete"
                    >
                        🗑️ Delete Issue
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <div class="loading-content">
                <div class="spinner"></div>
                <p>Processing...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    issue: {
        type: Object,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['back', 'update-status', 'delete-issue', 'request-analysis'])

function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getUrgencyText(severity) {
    const map = {
        'low': 'Non-urgent - Schedule when convenient',
        'medium': 'Moderate - Schedule within 2 weeks',
        'high': 'Urgent - Schedule within 1 week',
        'critical': 'Critical - Requires immediate attention',
        'Low': 'Non-urgent - Schedule when convenient',
        'Medium': 'Moderate - Schedule within 2 weeks',
        'High': 'Urgent - Schedule within 1 week',
        'Critical': 'Critical - Requires immediate attention'
    }
    return map[severity] || 'Consult with mechanic'
}

function getUrgencyClass(severity) {
    const level = severity?.toLowerCase()
    if (level === 'critical') return 'critical'
    if (level === 'high') return 'high'
    if (level === 'medium') return 'medium'
    return 'low'
}

function updateStatus(status) {
    emit('update-status', { issueId: props.issue.id, status })
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this issue? This action cannot be undone.')) {
        emit('delete-issue', props.issue.id)
    }
}
</script>

<style scoped>
.issue-detail {
    position: relative;
    min-height: 100vh;
    background: #f8f9fa;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: #667eea;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    padding: 10px 0;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.back-button:hover {
    color: #764ba2;
    transform: translateX(-5px);
}

.back-button span {
    font-size: 1.2rem;
}

.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 20px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

.detail-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f7ff;
    flex-wrap: wrap;
    gap: 20px;
}

.detail-header h1 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.8rem;
    flex: 1;
    min-width: 300px;
}

.meta-info {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    color: #666;
    font-size: 0.95rem;
}

.vehicle-display {
    background: #f0f7ff;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
    color: #0d6efd;
}

.odometer, .date {
    display: flex;
    align-items: center;
    gap: 6px;
}

.header-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 15px;
    min-width: 200px;
}

.status-display {
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-display.pending {
    background: #fff3cd;
    color: #856404;
}

.status-display.analyzed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-display.in_progress {
    background: #e2d9f3;
    color: #6f42c1;
}

.status-display.resolved {
    background: #d4edda;
    color: #155724;
}

.btn-ai-analyze {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
    width: 100%;
}

.btn-ai-analyze:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-ai-analyze:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.section {
    margin: 40px 0;
}

.section h2 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.4rem;
    font-weight: 600;
}

.description-box {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    border-left: 4px solid #6c757d;
    line-height: 1.8;
    color: #495057;
    white-space: pre-line;
}

.ai-section {
    margin: 40px 0;
    padding: 30px;
    background: #f0f7ff;
    border-radius: 12px;
    border: 2px solid #e3f2fd;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.section-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.6rem;
}

.severity-display {
    padding: 8px 24px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1rem;
    text-transform: uppercase;
}

.severity-display.low {
    background: #d4edda;
    color: #155724;
}

.severity-display.medium {
    background: #fff3cd;
    color: #856404;
}

.severity-display.high {
    background: #f8d7da;
    color: #721c24;
}

.severity-display.critical {
    background: #721c24;
    color: white;
}

.ai-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.ai-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.ai-card:hover {
    transform: translateY(-5px);
}

.ai-card h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-content {
    color: #495057;
    line-height: 1.7;
}

.card-content ul {
    margin: 0;
    padding-left: 20px;
}

.card-content li {
    margin-bottom: 10px;
    padding-left: 10px;
}

.card-content li:last-child {
    margin-bottom: 0;
}

.metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.metric:last-child {
    border-bottom: none;
}

.metric-label {
    font-weight: 500;
    color: #666;
}

.metric-value {
    font-weight: 600;
    color: #333;
}

.metric-value.urgency.low {
    color: #28a745;
}

.metric-value.urgency.medium {
    color: #ffc107;
}

.metric-value.urgency.high {
    color: #fd7e14;
}

.metric-value.urgency.critical {
    color: #dc3545;
}

.no-analysis {
    margin: 40px 0;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #dee2e6;
    text-align: center;
}

.no-analysis-content {
    max-width: 400px;
    margin: 0 auto;
}

.ai-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.no-analysis h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 1.4rem;
}

.no-analysis p {
    margin: 0 0 30px 0;
    color: #666;
    line-height: 1.6;
}

.btn-ai-large {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 15px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-ai-large:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-ai-large:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.action-section {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid #f0f7ff;
}

.action-section h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 1.3rem;
}

.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 12px 24px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-action:hover {
    transform: translateY(-2px);
    border-color: #667eea;
    background: #f8f9fa;
}

.btn-action.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.btn-action.resolved {
    border-color: #d4edda;
    color: #155724;
}

.btn-action.resolved:hover {
    background: #d4edda;
}

.btn-action.delete {
    border-color: #f8d7da;
    color: #721c24;
}

.btn-action.delete:hover {
    background: #f8d7da;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.loading-content {
    background: white;
    padding: 40px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.loading-content p {
    margin: 20px 0 0 0;
    color: #333;
    font-weight: 500;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .detail-content {
        padding: 20px;
    }
    
    .detail-header {
        flex-direction: column;
    }
    
    .header-actions {
        width: 100%;
        align-items: stretch;
    }
    
    .ai-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        justify-content: center;
    }
}
</style>