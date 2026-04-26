<?php

use App\DTOs\Catalogs\LaboratoryCategoryDTO;
use App\DTOs\Catalogs\LaboratoryExamDTO;
use App\Contracts\CatalogServiceContract;
use App\Models\LaboratoryCategory;
use App\Models\LaboratoryExam;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $tab = 'categories';
    public string $search = '';

    // Category Form
    public string $categoryName = '';
    public string $categoryDescription = '';
    public ?string $editingCategoryId = null;

    // Exam Form
    public string $examName = '';
    public string $examCategoryId = '';
    public string $examDescription = '';
    public string $examUnit = '';
    public string $examReferenceRange = '';
    public ?string $editingExamId = null;

    public function with(CatalogServiceContract $service): array
    {
        return [
            'categories' => LaboratoryCategory::query()
                ->when(
                    $this->search && $this->tab === 'categories',
                    fn ($q) => $q->where('name', 'like', "%{$this->search}%"),
                )
                ->latest()
                ->paginate(10, ['*'], 'catPage'),
            'exams' => LaboratoryExam::with('category')
                ->when(
                    $this->search && $this->tab === 'exams',
                    fn ($q) => $q->where('name', 'like', "%{$this->search}%"),
                )
                ->latest()
                ->paginate(10, ['*'], 'examPage'),
            'allCategories' => LaboratoryCategory::all(),
        ];
    }

    public function saveCategory(CatalogServiceContract $service): void
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
            'categoryDescription' => 'nullable|string',
        ]);

        $dto = new LaboratoryCategoryDTO(
            id: $this->editingCategoryId,
            name: $this->categoryName,
            description: $this->categoryDescription,
        );

        if ($this->editingCategoryId) {
            $service->updateLaboratoryCategory($this->editingCategoryId, $dto);
        } else {
            $service->createLaboratoryCategory($dto);
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'category-modal');
    }

    public function saveExam(CatalogServiceContract $service): void
    {
        $this->validate([
            'examName' => 'required|string|max:255',
            'examCategoryId' => 'required|exists:laboratory_categories,id',
            'examDescription' => 'nullable|string',
            'examUnit' => 'nullable|string|max:50',
            'examReferenceRange' => 'nullable|string|max:255',
        ]);

        $dto = new LaboratoryExamDTO(
            id: $this->editingExamId,
            category_id: $this->examCategoryId,
            name: $this->examName,
            description: $this->examDescription,
            unit: $this->examUnit,
            reference_range: $this->examReferenceRange,
        );

        if ($this->editingExamId) {
            $service->updateLaboratoryExam($this->editingExamId, $dto);
        } else {
            $service->createLaboratoryExam($dto);
        }

        $this->resetForm();
        $this->dispatch('modal-close', name: 'exam-modal');
    }

    public function editCategory(string $id): void
    {
        $category = LaboratoryCategory::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryDescription = $category->description ?? '';
        $this->dispatch('modal-show', name: 'category-modal');
    }

    public function editExam(string $id): void
    {
        $exam = LaboratoryExam::findOrFail($id);
        $this->editingExamId = $exam->id;
        $this->examName = $exam->name;
        $this->examCategoryId = $exam->category_id;
        $this->examDescription = $exam->description ?? '';
        $this->examUnit = $exam->unit ?? '';
        $this->examReferenceRange = $exam->reference_range ?? '';
        $this->dispatch('modal-show', name: 'exam-modal');
    }

    public function openCategoryModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'category-modal');
    }

    public function openExamModal(): void
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'exam-modal');
    }

    public function deleteCategory(string $id, CatalogServiceContract $service): void
    {
        $service->deleteLaboratoryCategory($id);
    }

    public function deleteExam(string $id, CatalogServiceContract $service): void
    {
        $service->deleteLaboratoryExam($id);
    }

    public function resetForm(): void
    {
        $this->reset([
            'categoryName',
            'categoryDescription',
            'editingCategoryId',
            'examName',
            'examCategoryId',
            'examDescription',
            'examUnit',
            'examReferenceRange',
            'editingExamId',
        ]);
    }
}; ?>

<section class="p-6">
    <div class="mb-6 flex justify-between items-end">
        <div>
            <flux:heading size="xl">{{ __('Laboratorios') }}</flux:heading>
            <flux:subheading>
                {{ __('Gestiona las categorías de análisis y los exámenes disponibles.') }}
            </flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="primary" icon="plus" wire:click="openCategoryModal">
                {{ __('Categoría') }}
            </flux:button>
            <flux:button variant="primary" icon="plus" wire:click="openExamModal">
                {{ __('Examen') }}
            </flux:button>
        </div>
    </div>

    <!-- Filtros y Tabs Simplificados -->
    <div class="mb-6 border-b border-gray-200 dark:border-zinc-700">
        <div class="flex gap-4">
            <button
                wire:click="$set('tab', 'categories')"
                class="py-2 px-4 border-b-2 transition-colors {{ $tab === 'categories' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                {{ __('Categorías') }}
            </button>
            <button
                wire:click="$set('tab', 'exams')"
                class="py-2 px-4 border-b-2 transition-colors {{ $tab === 'exams' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                {{ __('Exámenes') }}
            </button>
        </div>
    </div>

    @if ($tab === 'categories')
        <div class="space-y-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar categoría..."
                icon="magnifying-glass"
            />

            <div
                class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
            >
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50 dark:bg-zinc-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Nombre') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Descripción') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Acciones') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                        @foreach ($categories as $category)
                            <tr wire:key="cat-{{ $category->id }}">
                                <td class="px-6 py-4">{{ $category->name }}</td>
                                <td class="px-6 py-4 truncate max-w-xs">{{ $category->description }}</td>
                                <td class="px-6 py-4 flex gap-2">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        wire:click="editCategory('{{ $category->id }}')"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        wire:click="deleteCategory('{{ $category->id }}')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar examen..."
                icon="magnifying-glass"
            />

            <div
                class="bg-white dark:bg-zinc-900 shadow-md rounded-lg overflow-hidden border border-gray-100 dark:border-zinc-800"
            >
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50 dark:bg-zinc-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Nombre') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Categoría') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Unidad') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Ref.') }}
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider"
                            >
                                {{ __('Acciones') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                        @foreach ($exams as $exam)
                            <tr wire:key="exam-{{ $exam->id }}">
                                <td class="px-6 py-4">{{ $exam->name }}</td>
                                <td class="px-6 py-4">{{ $exam->category->name }}</td>
                                <td class="px-6 py-4">{{ $exam->unit }}</td>
                                <td class="px-6 py-4">{{ $exam->reference_range }}</td>
                                <td class="px-6 py-4 flex gap-2">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        wire:click="editExam('{{ $exam->id }}')"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        wire:click="deleteExam('{{ $exam->id }}')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
                    {{ $exams->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Modals (Flux modals are usually fine if published/standard) -->
    <flux:modal name="category-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingCategoryId ? __('Editar Categoría') : __('Nueva Categoría') }}
                </flux:heading>
                <flux:subheading>{{ __('Ingresa los detalles de la categoría de laboratorio.') }}</flux:subheading>
            </div>

            <form wire:submit="saveCategory" class="space-y-4">
                <flux:input wire:model="categoryName" :label="__('Nombre')" required />
                <flux:textarea wire:model="categoryDescription" :label="__('Descripción')" />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="exam-modal" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingExamId ? __('Editar Examen') : __('Nuevo Examen') }}</flux:heading>
                <flux:subheading>{{ __('Ingresa los detalles del examen clínico.') }}</flux:subheading>
            </div>

            <form wire:submit="saveExam" class="space-y-4">
                <flux:input wire:model="examName" :label="__('Nombre del Examen')" required />

                <flux:select
                    wire:model="examCategoryId"
                    :label="__('Categoría')"
                    placeholder="Selecciona una categoría"
                >
                    @foreach ($allCategories as $cat)
                        <flux:select.option :value="$cat->id">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="examDescription" :label="__('Descripción')" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="examUnit" :label="__('Unidad (ej. mg/dL)')" />
                    <flux:input wire:model="examReferenceRange" :label="__('Rango de Referencia')" />
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
