/**
 * Borrador del formulario "Agregar solicitud de laboratorio".
 *
 * Los exámenes marcados viven en el componente Livewire hasta que se pulsa
 * "Crear orden"; un F5 a medio armar los perdía. Se guardan en sessionStorage
 * (sobrevive al F5, se borra al cerrar la pestaña) y se restauran al cargar.
 * El servidor revalida el borrador contra el catálogo (restoreDraft).
 */

const WATCHED = ['pickedExams', 'newPresumptiveDiagnosis', 'newObservations', 'selectedExamId'];

function labOrderDraft(consultationId) {
    return {
        key: `lab-order-draft:${consultationId}`,

        init() {
            try {
                const raw = sessionStorage.getItem(this.key);
                if (raw) {
                    this.$wire.restoreDraft(JSON.parse(raw));
                }
            } catch {
                // Sin storage (modo privado / bloqueado): el formulario funciona igual, sin borrador.
            }

            WATCHED.forEach((prop) => this.$wire.$watch(prop, () => this.save()));
        },

        save() {
            const draft = {
                picked: this.$wire.pickedExams,
                diagnosis: this.$wire.newPresumptiveDiagnosis,
                observations: this.$wire.newObservations,
                activeExamId: this.$wire.selectedExamId,
            };
            const isEmpty = Object.keys(draft.picked ?? {}).length === 0 && !draft.diagnosis && !draft.observations;

            try {
                if (isEmpty) {
                    sessionStorage.removeItem(this.key);
                } else {
                    sessionStorage.setItem(this.key, JSON.stringify(draft));
                }
            } catch {
                // Ignorar: el borrador es una comodidad, no un requisito.
            }
        },
    };
}

export function initLabOrderDraft() {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('labOrderDraft', labOrderDraft);
    });
}
