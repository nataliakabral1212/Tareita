<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Servicio } from './Servicios.vue'
import type { Categoria } from './Categorias.vue'

interface ServicioForm {
  nombre: string
  categoriaId: number
  duracion: number
  precio: number
  descripcion: string
  estado: 'activo' | 'inactivo'
}

const props = defineProps<{
  show: boolean
  action: 'crear' | 'editar'
  servicio: Servicio | null
  categorias: Categoria[]
  showDelete: boolean
  servicioAEliminar: Servicio | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'closeDelete'): void
  (e: 'guardar', form: ServicioForm): void
  (e: 'eliminar'): void
}>()

const form = ref<ServicioForm>({
  nombre: '', categoriaId: 0, duracion: 30, precio: 0, descripcion: '', estado: 'activo'
})

watch(() => props.show, (val) => {
  if (val) {
    form.value = props.action === 'editar' && props.servicio
      ? { ...props.servicio }
      : { nombre: '', categoriaId: props.categorias[0]?.id ?? 0, duracion: 30, precio: 0, descripcion: '', estado: 'activo' }
  }
})

function guardar(): void {
  if (!form.value.nombre.trim() || !form.value.categoriaId) return
  emit('guardar', { ...form.value })
}

const inputStyle = "width:100%;padding:11px 14px;border:1.5px solid rgba(212,168,176,0.5);border-radius:12px;font-size:13px;color:#7A4E38;font-family:'Jost',sans-serif;outline:none;box-sizing:border-box;background:#FDFAF7;"
</script>

<template>
  <Teleport to="body">

    <!-- ── Modal Crear/Editar ── -->
    <Transition name="fade">
      <div v-if="show"
        style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;"
        @click.self="emit('close')">

        <div style="position:absolute;inset:0;background:rgba(122,78,56,0.18);backdrop-filter:blur(4px);"></div>

        <div style="position:relative;background:white;border-radius:24px;padding:36px;width:100%;max-width:500px;box-shadow:0 24px 60px rgba(122,78,56,0.18);font-family:'Jost',sans-serif;max-height:90vh;overflow-y:auto;">

          <!-- Header -->
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
            <div>
              <h3 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:22px;font-weight:700;color:#7A4E38;margin:0;">
                {{ action === 'crear' ? 'Nuevo Servicio' : 'Editar Servicio' }}
              </h3>
              <p style="font-size:12px;color:#A896A0;margin:3px 0 0;">
                {{ action === 'crear' ? 'Completa los datos del servicio' : 'Modifica los datos del servicio' }}
              </p>
            </div>
            <button @click="emit('close')"
              style="width:32px;height:32px;border-radius:50%;border:none;background:#F0E8DC;display:flex;align-items:center;justify-content:center;cursor:pointer;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7A4E38" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <!-- Campos -->
          <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Nombre -->
            <div>
              <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Nombre</label>
              <input v-model="form.nombre" type="text" placeholder="Ej. Corte de cabello" :style="inputStyle"
                @focus="($event.target as HTMLInputElement).style.borderColor='#C4A882'"
                @blur="($event.target as HTMLInputElement).style.borderColor='rgba(212,168,176,0.5)'" />
            </div>

            <!-- Categoría -->
            <div>
              <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Categoría</label>
              <select v-model="form.categoriaId" :style="inputStyle"
                @focus="($event.target as HTMLSelectElement).style.borderColor='#C4A882'"
                @blur="($event.target as HTMLSelectElement).style.borderColor='rgba(212,168,176,0.5)'">
                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
              </select>
            </div>

            <!-- Duración y Precio en fila -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Duración (min)</label>
                <input v-model.number="form.duracion" type="number" min="1" placeholder="45" :style="inputStyle"
                  @focus="($event.target as HTMLInputElement).style.borderColor='#C4A882'"
                  @blur="($event.target as HTMLInputElement).style.borderColor='rgba(212,168,176,0.5)'" />
              </div>
              <div>
                <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Precio ($)</label>
                <input v-model.number="form.precio" type="number" min="0" placeholder="150" :style="inputStyle"
                  @focus="($event.target as HTMLInputElement).style.borderColor='#C4A882'"
                  @blur="($event.target as HTMLInputElement).style.borderColor='rgba(212,168,176,0.5)'" />
              </div>
            </div>

            <!-- Descripción -->
            <div>
              <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:6px;">Descripción</label>
              <textarea v-model="form.descripcion" placeholder="Describe el servicio..." rows="3"
                :style="inputStyle + 'resize:vertical;'"
                @focus="($event.target as HTMLTextAreaElement).style.borderColor='#C4A882'"
                @blur="($event.target as HTMLTextAreaElement).style.borderColor='rgba(212,168,176,0.5)'"></textarea>
            </div>

            <!-- Estado -->
            <div>
              <label style="font-size:11px;font-weight:600;color:#7A4E38;letter-spacing:0.08em;text-transform:uppercase;display:block;margin-bottom:10px;">Estado</label>
              <div style="display:flex;gap:10px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border-radius:50px;border:1.5px solid;transition:all .2s;"
                  :style="form.estado === 'activo' ? 'border-color:#3A7A50;background:rgba(134,188,149,0.1);' : 'border-color:rgba(212,168,176,0.4);background:white;'">
                  <input type="radio" v-model="form.estado" value="activo" style="accent-color:#3A7A50;" />
                  <span style="font-size:13px;font-weight:600;" :style="form.estado === 'activo' ? 'color:#3A7A50;' : 'color:#A896A0;'">Activo</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 18px;border-radius:50px;border:1.5px solid;transition:all .2s;"
                  :style="form.estado === 'inactivo' ? 'border-color:#B05070;background:rgba(176,80,112,0.08);' : 'border-color:rgba(212,168,176,0.4);background:white;'">
                  <input type="radio" v-model="form.estado" value="inactivo" style="accent-color:#B05070;" />
                  <span style="font-size:13px;font-weight:600;" :style="form.estado === 'inactivo' ? 'color:#B05070;' : 'color:#A896A0;'">Inactivo</span>
                </label>
              </div>
            </div>

          </div>

          <!-- Botones -->
          <div style="display:flex;gap:10px;margin-top:28px;">
            <button @click="emit('close')"
              style="flex:1;padding:12px;border:1.5px solid rgba(212,168,176,0.5);border-radius:50px;background:white;font-size:13px;font-weight:600;color:#A896A0;cursor:pointer;font-family:'Jost',sans-serif;">
              Cancelar
            </button>
            <button @click="guardar"
              style="flex:1;padding:12px;border:none;border-radius:50px;font-size:13px;font-weight:600;color:white;cursor:pointer;font-family:'Jost',sans-serif;background:linear-gradient(135deg,#D4A8B0 0%,#C4A882 50%,#7A4E38 100%);box-shadow:0 6px 18px rgba(122,78,56,0.25);">
              {{ action === 'crear' ? 'Crear Servicio' : 'Guardar Cambios' }}
            </button>
          </div>

        </div>
      </div>
    </Transition>

    <!-- ── Modal Eliminar ── -->
    <Transition name="fade">
      <div v-if="showDelete"
        style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;"
        @click.self="emit('closeDelete')">

        <div style="position:absolute;inset:0;background:rgba(122,78,56,0.18);backdrop-filter:blur(4px);"></div>

        <div style="position:relative;background:white;border-radius:24px;padding:36px;width:100%;max-width:400px;box-shadow:0 24px 60px rgba(122,78,56,0.18);font-family:'Jost',sans-serif;text-align:center;">

          <div style="width:56px;height:56px;border-radius:50%;background:#FFF0F3;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B05070" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6"/><path d="M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
          </div>

          <h3 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:22px;font-weight:700;color:#7A4E38;margin:0 0 8px;">Eliminar Servicio</h3>
          <p style="font-size:13px;color:#A896A0;margin:0 0 28px;">
            ¿Estás seguro de eliminar <strong style="color:#7A4E38;">{{ servicioAEliminar?.nombre }}</strong>? Esta acción no se puede deshacer.
          </p>

          <div style="display:flex;gap:10px;">
            <button @click="emit('closeDelete')"
              style="flex:1;padding:12px;border:1.5px solid rgba(212,168,176,0.5);border-radius:50px;background:white;font-size:13px;font-weight:600;color:#A896A0;cursor:pointer;font-family:'Jost',sans-serif;">
              Cancelar
            </button>
            <button @click="emit('eliminar')"
              style="flex:1;padding:12px;border:none;border-radius:50px;font-size:13px;font-weight:600;color:white;cursor:pointer;font-family:'Jost',sans-serif;background:linear-gradient(135deg,#D4A8B0,#B05070);box-shadow:0 6px 18px rgba(176,80,112,0.3);">
              Sí, eliminar
            </button>
          </div>

        </div>
      </div>
    </Transition>

  </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
