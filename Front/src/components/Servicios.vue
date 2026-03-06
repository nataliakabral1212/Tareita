<script setup lang="ts">
const emit = defineEmits<{ (e: 'navigate', route: string): void }>()
import { ref, computed, onMounted } from 'vue'
import MenuLateral from './MenuLateral.vue'
import ModalServicio from './ModalServicio.vue'
import type { Categoria } from './Categorias.vue'

export interface Servicio {
  id: number
  nombre: string
  categoriaId: number
  duracion: number
  precio: number
  descripcion: string
  estado: 'activo' | 'inactivo'
}

interface ServicioForm {
  nombre: string
  categoriaId: number
  duracion: number
  precio: number
  descripcion: string
  estado: 'activo' | 'inactivo'
}

const BASE_URL = 'http://127.0.0.1:8000/api'

// ── State ──────────────────────────────────────────────────────
const servicios         = ref<Servicio[]>([])
const categorias        = ref<Categoria[]>([])
const cargando          = ref<boolean>(false)
const error             = ref<string>('')
const busqueda          = ref<string>('')
const showModal         = ref<boolean>(false)
const modalAction       = ref<'crear' | 'editar'>('crear')
const showDeleteModal   = ref<boolean>(false)
const servicioAEliminar = ref<Servicio | null>(null)
const selectedServicio  = ref<Servicio | null>(null)

// ── Computed ───────────────────────────────────────────────────
const serviciosFiltrados = computed<Servicio[]>(() => {
  if (!busqueda.value) return servicios.value
  const t = busqueda.value.toLowerCase()
  return servicios.value.filter((s: Servicio) =>
    s.nombre.toLowerCase().includes(t) ||
    s.descripcion.toLowerCase().includes(t) ||
    nombreCategoria(s.categoriaId).toLowerCase().includes(t)
  )
})

// ── Helpers ────────────────────────────────────────────────────
function nombreCategoria(id: number): string {
  return categorias.value.find(c => c.id === id)?.nombre ?? '—'
}

// ── API: Cargar datos ─────────────────────────────────────────
async function cargarDatos(): Promise<void> {
  cargando.value = true
  error.value = ''
  try {
    // Carga servicios y categorías en paralelo
    const [resServicios, resCategorias] = await Promise.all([
      fetch(`${BASE_URL}/servicios`,  { headers: { 'Accept': 'application/json' } }),
      fetch(`${BASE_URL}/categorias`, { headers: { 'Accept': 'application/json' } }),
    ])
    if (!resServicios.ok || !resCategorias.ok) throw new Error('Error al cargar datos')

    const dataServicios  = await resServicios.json()
    const dataCategorias = await resCategorias.json()

    // El backend devuelve categoria_id, mapeamos a categoriaId para el tipo Servicio
    servicios.value  = dataServicios.map((s: any) => ({
      ...s,
      categoriaId: s.categoria_id ?? s.categoriaId
    }))
    categorias.value = dataCategorias
  } catch (e) {
    error.value = 'No se pudo conectar con el servidor.'
  } finally {
    cargando.value = false
  }
}

// ── API: Crear servicio ────────────────────────────────────────
async function crearServicio(form: ServicioForm): Promise<void> {
  const res = await fetch(`${BASE_URL}/servicios`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(form)
  })
  if (!res.ok) {
    const data = await res.json()
    throw new Error(data.message ?? 'Error al crear servicio')
  }
  const data = await res.json()
  const nuevo = { ...data.data, categoriaId: data.data.categoria_id ?? data.data.categoriaId }
  servicios.value.unshift(nuevo)
}

// ── Methods ────────────────────────────────────────────────────
function openCrear(): void {
  modalAction.value = 'crear'
  selectedServicio.value = null
  showModal.value = true
}

function openEditar(s: Servicio): void {
  modalAction.value = 'editar'
  selectedServicio.value = { ...s }
  showModal.value = true
}

async function onGuardar(form: ServicioForm): Promise<void> {
  try {
    if (modalAction.value === 'crear') {
      await crearServicio(form)
    }
    showModal.value = false
  } catch (e: any) {
    alert(e.message)
  }
}

function pedirEliminar(s: Servicio): void {
  servicioAEliminar.value = s
  showDeleteModal.value = true
}

function onEliminar(): void {
  if (servicioAEliminar.value) {
    servicios.value = servicios.value.filter((s: Servicio) => s.id !== servicioAEliminar.value!.id)
  }
  showDeleteModal.value = false
  servicioAEliminar.value = null
}

onMounted(cargarDatos)
</script>

<template>
  <div style="position:fixed;inset:0;display:flex;font-family:'Jost',sans-serif;background:#F0E8DC;overflow:hidden;">
    <div style="position:absolute;inset:0;pointer-events:none;overflow:hidden;">
      <svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;inset:0;width:100%;height:100%" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
        <ellipse cx="1380" cy="820" rx="50" ry="120" fill="#C4A882" opacity="0.11" transform="rotate(15,1380,820)"/>
        <ellipse cx="1360" cy="40"  rx="35" ry="85"  fill="#D47878" opacity="0.10" transform="rotate(40,1360,40)"/>
        <ellipse cx="40"   cy="880" rx="45" ry="110" fill="#D4A8B0" opacity="0.12" transform="rotate(-30,40,880)"/>
      </svg>
    </div>

    <MenuLateral active-route="servicios" @navigate="emit('navigate', $event)" />

    <main style="flex:1;display:flex;flex-direction:column;padding:28px 32px;min-width:0;position:relative;z-index:5;overflow:hidden;">
      <header style="display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="text-align:left;">
          <h2 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:30px;font-weight:700;color:#7A4E38;margin:0;line-height:1.1">Gestión de Servicios</h2>
          <p style="font-size:12px;color:#A896A0;margin:3px 0 0">{{ servicios.length }} servicios registrados</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-left:auto;">
          <div style="display:flex;align-items:center;gap:8px;background:white;border:1.5px solid rgba(212,168,176,0.4);border-radius:50px;padding:8px 16px;box-shadow:0 2px 8px rgba(122,78,56,0.06);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#C4A882" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="busqueda" type="text" placeholder="Buscar servicio..."
              style="border:none;outline:none;background:transparent;font-size:13px;color:#7A4E38;font-family:'Jost',sans-serif;width:170px;" />
          </div>
          <button @click="openCrear"
            style="display:flex;align-items:center;gap:7px;padding:10px 22px;border:none;border-radius:50px;cursor:pointer;font-size:12px;font-weight:600;letter-spacing:0.05em;color:white;font-family:'Jost',sans-serif;background:linear-gradient(135deg,#D4A8B0 0%,#C4A882 50%,#7A4E38 100%);box-shadow:0 6px 18px rgba(122,78,56,0.25);white-space:nowrap;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Servicio
          </button>
        </div>
      </header>

      <div v-if="error" style="margin-bottom:12px;padding:10px 16px;border-radius:12px;background:rgba(212,120,120,0.1);border:1px solid rgba(212,120,120,0.3);color:#7A4E38;font-size:13px;">
        ⚠️ {{ error }}
      </div>

      <div style="flex:1;background:white;border-radius:20px;box-shadow:0 8px 40px rgba(122,78,56,0.1);overflow:auto;">
        <div v-if="cargando" style="display:flex;align-items:center;justify-content:center;padding:80px 20px;color:#A896A0;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D4A8B0" stroke-width="2" style="animation:spin 1s linear infinite;margin-right:10px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>
          Cargando servicios...
        </div>

        <div v-else-if="serviciosFiltrados.length === 0" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 20px;color:#A896A0;">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D4A8B0" stroke-width="1.2" style="margin-bottom:12px"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 8v4l3 3"/></svg>
          <p style="font-size:14px;">No se encontraron servicios</p>
        </div>

        <table v-else style="width:100%;border-collapse:collapse;font-size:13px;">
          <colgroup>
            <col style="width:22%" /><col style="width:16%" /><col style="width:10%" />
            <col style="width:10%" /><col style="width:28%" /><col style="width:8%" /><col style="width:6%" />
          </colgroup>
          <thead>
            <tr style="background:linear-gradient(135deg,#D4A8B0 0%,#C4A882 55%,#9A7A5A 100%);">
              <th style="padding:14px 20px;text-align:left;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Nombre</th>
              <th style="padding:14px 20px;text-align:left;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Categoría</th>
              <th style="padding:14px 20px;text-align:left;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Duración</th>
              <th style="padding:14px 20px;text-align:left;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Precio</th>
              <th style="padding:14px 20px;text-align:left;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Descripción</th>
              <th style="padding:14px 20px;text-align:center;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Estado</th>
              <th style="padding:14px 20px;text-align:center;color:white;font-size:10px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in serviciosFiltrados" :key="s.id"
              style="border-bottom:1px solid rgba(212,168,176,0.15);transition:background .15s;"
              @mouseenter="($event.currentTarget as HTMLElement).style.background='rgba(240,232,220,0.4)'"
              @mouseleave="($event.currentTarget as HTMLElement).style.background='transparent'">
              <td style="padding:16px 20px;vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:12px;">
                  <div style="width:36px;height:36px;min-width:36px;border-radius:50%;background:linear-gradient(135deg,#D4A8B0,#C4A882);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;">
                    {{ s.nombre.charAt(0) }}
                  </div>
                  <span style="font-weight:600;color:#7A4E38;font-size:13px;">{{ s.nombre }}</span>
                </div>
              </td>
              <td style="padding:16px 20px;vertical-align:middle;text-align:left;">
                <span style="background:rgba(196,168,130,0.15);color:#8B6B3D;font-size:11px;font-weight:600;padding:4px 10px;border-radius:50px;">
                  {{ nombreCategoria(s.categoriaId) }}
                </span>
              </td>
              <td style="padding:16px 20px;vertical-align:middle;color:#5a4040;font-size:13px;">{{ s.duracion }} min</td>
              <td style="padding:16px 20px;vertical-align:middle;font-weight:600;color:#7A4E38;font-size:13px;">${{ s.precio }}</td>
              <td style="padding:16px 20px;vertical-align:middle;color:#5a4040;font-size:13px;">{{ s.descripcion }}</td>
              <td style="padding:16px 20px;vertical-align:middle;text-align:center;">
                <span :style="{
                  background: s.estado === 'activo' ? 'rgba(134,188,149,0.15)' : 'rgba(212,168,176,0.2)',
                  color:      s.estado === 'activo' ? '#3A7A50' : '#B05070',
                  fontSize: '11px', fontWeight: '600', padding: '4px 10px', borderRadius: '50px'
                }">
                  {{ s.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td style="padding:16px 20px;vertical-align:middle;">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                  <button @click="openEditar(s)" title="Editar"
                    style="width:48px;height:48px;border-radius:10px;border:none;background:#C4A882;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;"
                    @mouseenter="($event.currentTarget as HTMLElement).style.background='#8B6B3D'"
                    @mouseleave="($event.currentTarget as HTMLElement).style.background='#C4A882'">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button @click="pedirEliminar(s)" title="Eliminar"
                    style="width:48px;height:48px;border-radius:10px;border:none;background:#D4A8B0;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;"
                    @mouseenter="($event.currentTarget as HTMLElement).style.background='#B05070'"
                    @mouseleave="($event.currentTarget as HTMLElement).style.background='#D4A8B0'">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <ModalServicio
      :show="showModal"
      :action="modalAction"
      :servicio="selectedServicio"
      :categorias="categorias"
      :show-delete="showDeleteModal"
      :servicio-a-eliminar="servicioAEliminar"
      @close="showModal = false"
      @close-delete="showDeleteModal = false"
      @guardar="onGuardar"
      @eliminar="onEliminar"
    />
  </div>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
