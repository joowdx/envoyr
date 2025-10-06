/**
 * Action Sequence Stepper JavaScript utilities
 * Provides additional functionality for the stepper component
 */

window.StepperUtils = {
    /**
     * Animate step addition
     */
    animateStepAdd(element) {
        if (!element) return;

        element.style.opacity = '0';
        element.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            element.style.transition = 'all 0.3s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateX(0)';
        }, 10);
    },

    /**
     * Animate step removal
     */
    animateStepRemove(element, callback) {
        if (!element) {
            callback?.();
            return;
        }

        element.style.transition = 'all 0.3s ease-in';
        element.style.opacity = '0';
        element.style.transform = 'translateX(20px)';

        setTimeout(() => {
            callback?.();
        }, 300);
    },

    /**
     * Get next available actions based on current sequence
     */
    getNextAvailableActions(allActions, selectedActions) {
        const selectedIds = selectedActions.map(a => a.id);

        return allActions.filter(action => {
            // Don't include already selected actions
            return !selectedIds.includes(action.id);
        });
    },

    /**
     * Generate workflow summary
     */
    generateWorkflowSummary(selectedActions) {
        if (selectedActions.length === 0) {
            return {
                steps: 0,
                estimatedDuration: 0,
                complexity: 'none',
                summary: 'No actions selected'
            };
        }

        const complexity = selectedActions.length > 5 ? 'high' :
            selectedActions.length > 2 ? 'medium' : 'low';

        return {
            steps: selectedActions.length,
            estimatedDuration: selectedActions.length * 30, // 30 minutes per step
            complexity: complexity,
            summary: `${selectedActions.length} step workflow with ${complexity} complexity`
        };
    },

    /**
     * Export workflow configuration
     */
    exportWorkflow(selectedActions, metadata = {}) {
        return {
            version: '1.0',
            created: new Date().toISOString(),
            metadata: metadata,
            sequence: selectedActions.map((action, index) => ({
                step: index + 1,
                action_id: action.id,
                action_name: action.name,
                status_name: action.status_name || 'Processing'
            }))
        };
    },

    /**
     * Import workflow configuration
     */
    importWorkflow(workflowData, allActions) {
        if (!workflowData.sequence) {
            throw new Error('Invalid workflow data: missing sequence');
        }

        const selectedActions = [];

        for (const step of workflowData.sequence) {
            const action = allActions.find(a => a.id === step.action_id);
            if (action) {
                selectedActions.push(action);
            }
        }

        return selectedActions;
    },

    /**
     * Highlight related actions
     */
    highlightActions(actionId, selectedActions, highlight = true) {
        const elements = document.querySelectorAll(`[data-action-id="${actionId}"]`);

        elements.forEach(element => {
            if (highlight) {
                element.classList.add('action-highlight');
            } else {
                element.classList.remove('action-highlight');
            }
        });
    },

    /**
     * Show action details in tooltip
     */
    showActionTooltip(action, event) {
        const tooltip = document.createElement('div');
        tooltip.className = 'action-tooltip';
        tooltip.innerHTML = `
            <div class="font-semibold">${action.name}</div>
            <div class="text-sm text-gray-600">${action.status_name || 'Processing'}</div>
        `;

        // Position and show tooltip
        document.body.appendChild(tooltip);

        const rect = event.target.getBoundingClientRect();
        tooltip.style.position = 'fixed';
        tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;
        tooltip.style.left = `${rect.left}px`;
        tooltip.style.zIndex = '9999';

        // Remove tooltip after delay
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 3000);
    },

    /**
     * Keyboard navigation support
     */
    setupKeyboardNavigation(container) {
        if (!container) return;

        container.addEventListener('keydown', (event) => {
            const actions = container.querySelectorAll('.action-card');
            const currentIndex = Array.from(actions).findIndex(
                action => action === document.activeElement
            );

            switch (event.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    event.preventDefault();
                    const nextIndex = (currentIndex + 1) % actions.length;
                    actions[nextIndex]?.focus();
                    break;

                case 'ArrowLeft':
                case 'ArrowUp':
                    event.preventDefault();
                    const prevIndex = currentIndex === 0 ? actions.length - 1 : currentIndex - 1;
                    actions[prevIndex]?.focus();
                    break;

                case 'Enter':
                case ' ':
                    event.preventDefault();
                    if (currentIndex >= 0) {
                        actions[currentIndex].click();
                    }
                    break;
            }
        });
    },

    /**
     * Validate workflow sequence
     */
    validateWorkflow(selectedActions) {
        const errors = [];
        const warnings = [];

        if (selectedActions.length === 0) {
            errors.push('At least one action is required');
        }

        if (selectedActions.length > 10) {
            warnings.push('Workflow has many steps - consider breaking it down');
        }

        // Check for duplicate actions
        const actionIds = selectedActions.map(a => a.id);
        const duplicates = actionIds.filter((id, index) => actionIds.indexOf(id) !== index);

        if (duplicates.length > 0) {
            warnings.push('Workflow contains duplicate actions');
        }

        return {
            isValid: errors.length === 0,
            errors: errors,
            warnings: warnings
        };
    },

    /**
     * Auto-save workflow draft
     */
    autoSave(selectedActions, processName = null) {
        const draftKey = `workflow_draft_${processName || 'default'}`;
        const draftData = {
            actions: selectedActions,
            timestamp: Date.now(),
            processName: processName
        };

        try {
            localStorage.setItem(draftKey, JSON.stringify(draftData));
            console.log('Workflow draft saved');
        } catch (e) {
            console.warn('Failed to save workflow draft:', e);
        }
    },

    /**
     * Load workflow draft
     */
    loadDraft(processName = null) {
        const draftKey = `workflow_draft_${processName || 'default'}`;

        try {
            const draftData = localStorage.getItem(draftKey);
            if (draftData) {
                const parsed = JSON.parse(draftData);
                // Only return drafts from the last 24 hours
                if (Date.now() - parsed.timestamp < 24 * 60 * 60 * 1000) {
                    return parsed.actions || [];
                }
            }
        } catch (e) {
            console.warn('Failed to load workflow draft:', e);
        }

        return [];
    }
};

// Initialize stepper utilities when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Setup keyboard navigation for all stepper containers
    document.querySelectorAll('.action-sequence-stepper').forEach(container => {
        StepperUtils.setupKeyboardNavigation(container);
    });

    // Add CSS for tooltips and animations
    if (!document.querySelector('#stepper-tooltip-styles')) {
        const style = document.createElement('style');
        style.id = 'stepper-tooltip-styles';
        style.textContent = `
            .action-tooltip {
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                max-width: 200px;
                pointer-events: none;
                opacity: 0;
                animation: tooltipFadeIn 0.2s ease-out forwards;
            }
            
            @keyframes tooltipFadeIn {
                from { opacity: 0; transform: translateY(5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .action-highlight {
                box-shadow: 0 0 0 2px #fbbf24 !important;
                background-color: #fef3c7 !important;
            }
            
            .dark .action-highlight {
                background-color: #451a03 !important;
            }
        `;
        document.head.appendChild(style);
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StepperUtils;
}