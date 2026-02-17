<template>
    <div class="create-issue-form">
        <div class="form-container">
            <div class="form-header">
                <h2>Report New Vehicle Issue</h2>
                <p>Fill in the details below and our AI will analyze your issue</p>
            </div>

            <form @submit.prevent="submitForm" class="issue-form">
                <!-- Vehicle Information -->
                <div class="form-section">
                    <h3>Vehicle Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="vehicle_make">Make *</label>
                            <input
                                v-model="form.vehicle_make"
                                type="text"
                                id="vehicle_make"
                                placeholder="e.g., Toyota"
                                required
                                :class="{ 'error': errors.vehicle_make }"
                            >
                            <span v-if="errors.vehicle_make" class="error-message">
                                {{ errors.vehicle_make[0] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="vehicle_model">Model *</label>
                            <input
                                v-model="form.vehicle_model"
                                type="text"
                                id="vehicle_model"
                                placeholder="e.g., Camry"
                                required
                                :class="{ 'error': errors.vehicle_model }"
                            >
                            <span v-if="errors.vehicle_model" class="error-message">
                                {{ errors.vehicle_model[0] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="vehicle_year">Year *</label>
                            <input
                                v-model="form.vehicle_year"
                                type="number"
                                id="vehicle_year"
                                :min="1900"
                                :max="new Date().getFullYear() + 1"
                                required
                                :class="{ 'error': errors.vehicle_year }"
                            >
                            <span v-if="errors.vehicle_year" class="error-message">
                                {{ errors.vehicle_year[0] }}
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="odometer_reading">Odometer Reading</label>
                            <input
                                v-model="form.odometer_reading"
                                type="text"
                                id="odometer_reading"
                                placeholder="e.g., 75,000 miles"
                            >
                        </div>
                    </div>
                </div>

                <!-- Issue Details -->
                <div class="form-section">
                    <h3>Issue Details</h3>
                    <div class="form-group">
                        <label for="title">Issue Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            id="title"
                            placeholder="Brief description of the issue"
                            required
                            :class="{ 'error': errors.title }"
                        >
                        <span v-if="errors.title" class="error-message">
                            {{ errors.title[0] }}
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="description">Detailed Description *</label>
                        <textarea
                            v-model="form.description"
                            id="description"
                            rows="8"
                            placeholder="Describe the issue in detail:
• When did it start?
• Any warning lights?
• Unusual sounds or smells?
• When does it occur (cold start, while driving, etc.)?
• Any recent repairs or maintenance?"
                            required
                            :class="{ 'error': errors.description }"
                        ></textarea>
                        <span v-if="errors.description" class="error-message">
                            {{ errors.description[0] }}
                        </span>
                        <div class="char-count">
                            {{ form.description.length }}/1000 characters
                        </div>
                    </div>
                </div>

                <!-- AI Analysis Preview -->
                <div v-if="aiPreview" class="ai-preview-section">
                    <h3>🤖 AI Analysis Preview</h3>
                    <div class="ai-preview-content">
                        <p><strong>Note:</strong> After submission, our AI will analyze your issue and provide:</p>
                        <ul>
                            <li>Severity assessment</li>
                            <li>Probable causes</li>
                            <li>Immediate actions</li>
                            <li>Repair recommendations</li>
                            <li>Cost estimates</li>
                        </ul>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button 
                        type="button" 
                        @click="$emit('cancel')" 
                        class="btn-secondary"
                        :disabled="submitting"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="btn-primary"
                        :disabled="submitting"
                    >
                        <span v-if="submitting">
                            <span class="spinner-small"></span> Submitting...
                        </span>
                        <span v-else>
                            Submit Issue & Get AI Analysis
                        </span>
                    </button>
                </div>

                <div v-if="submitError" class="submit-error">
                    <div class="error-icon">⚠️</div>
                    <p>{{ submitError }}</p>
                </div>
            </form>

            <!-- Success Message -->
            <div v-if="success" class="success-message">
                <div class="success-icon">✅</div>
                <h3>Issue Reported Successfully!</h3>
                <p>Your vehicle issue has been submitted. Our AI is now analyzing it.</p>
                <p class="ai-processing">
                    🤖 AI analysis in progress... This may take a moment.
                </p>
                <button @click="handleSuccess" class="btn-success">
                    View All Issues
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'

const emit = defineEmits(['issue-created', 'cancel'])

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
const form = reactive({
    title: '',
    description: '',
    vehicle_make: '',
    vehicle_model: '',
    vehicle_year: new Date().getFullYear(),
    odometer_reading: ''
})

const errors = ref({})
const submitting = ref(false)
const success = ref(false)
const submitError = ref('')

// Computed
const aiPreview = computed(() => 
    form.title && form.description && form.vehicle_make && form.vehicle_model
)

// Methods
async function submitForm() {
    submitting.value = true
    errors.value = {}
    submitError.value = ''

    try {
        const response = await api.post('/vehicle-issues', form)
        
        // Clear form
        Object.keys(form).forEach(key => {
            if (key !== 'vehicle_year') {
                form[key] = ''
            } else {
                form[key] = new Date().getFullYear()
            }
        })
        
        success.value = true
        emit('issue-created', response.data.data)
        
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            submitError.value = error.response?.data?.message || 'Failed to submit issue. Please try again.'
        }
    } finally {
        submitting.value = false
    }
}

function handleSuccess() {
    success.value = false
    emit('issue-created')
}

// Watch description length
watch(() => form.description, (newDesc) => {
    if (newDesc.length > 1000) {
        form.description = newDesc.substring(0, 1000)
    }
})
</script>

<style scoped>
.create-issue-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f7ff;
}

.form-header h2 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.8rem;
}

.form-header p {
    margin: 0;
    color: #666;
    font-size: 1rem;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #444;
    font-size: 1.2rem;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error,
.form-group textarea.error {
    border-color: #dc3545;
}

.error-message {
    display: block;
    margin-top: 6px;
    color: #dc3545;
    font-size: 0.85rem;
}

.char-count {
    text-align: right;
    margin-top: 5px;
    font-size: 0.85rem;
    color: #666;
}

.ai-preview-section {
    background: #f0f7ff;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
    border-left: 4px solid #667eea;
}

.ai-preview-section h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 1.2rem;
}

.ai-preview-content p {
    margin: 0 0 10px 0;
    color: #495057;
}

.ai-preview-content ul {
    margin: 10px 0;
    padding-left: 20px;
    color: #495057;
}

.ai-preview-content li {
    margin-bottom: 5px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.btn-primary, .btn-secondary, .btn-success {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #5a6268;
}

.btn-primary:disabled, .btn-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-right: 8px;
    vertical-align: middle;
}

.submit-error {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.error-icon {
    font-size: 1.5rem;
}

.success-message {
    text-align: center;
    padding: 40px 20px;
    animation: fadeIn 0.5s ease-in;
}

.success-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.success-message h3 {
    margin: 0 0 15px 0;
    color: #155724;
    font-size: 1.5rem;
}

.success-message p {
    margin: 0 0 10px 0;
    color: #495057;
}

.ai-processing {
    color: #0d6efd !important;
    font-weight: 500;
    margin: 20px 0 !important;
    padding: 10px;
    background: #e7f5ff;
    border-radius: 6px;
}

.btn-success {
    margin-top: 20px;
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .create-issue-form {
        padding: 10px;
    }
    
    .form-container {
        padding: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
    }
}
</style>