<template>
  <div class="space-y-4">

    <!-- ── TOOLBAR ──────────────────────────────────────── -->
    <div class="flex flex-wrap gap-3 items-center">

      <div class="relative">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 text-xs"></i>
        <input v-model="search" placeholder="Nom, club…"
               class="input-admin pl-8 w-52" />
      </div>

      <select v-model="teamFilter" class="input-admin">
        <option value="">Toutes les équipes</option>
        <option v-for="t in TEAM_OPTIONS" :key="t.fr" :value="t.fr">
          {{ t.fr }}
        </option>
      </select>

      <select v-model="posFilter" class="input-admin">
        <option value="">Tous postes</option>
        <option v-for="p in POSITIONS" :key="p" :value="p">{{ p }}</option>
      </select>

      <div class="ml-auto flex items-center gap-2">
        <span class="text-gray-600 text-xs">{{ filtered.length }} / {{ players.length }}</span>
        <button @click="showFetchModal = true"
                class="btn-admin-ghost gap-2 text-sm">
          <i class="fas fa-images"></i>
          Auto-photos
        </button>
        <button @click="openAdd"
                class="btn-admin-primary gap-2">
          <i class="fas fa-user-plus"></i>
          Ajouter un joueur
        </button>
      </div>
    </div>

    <!-- ── TABLE ─────────────────────────────────────────── -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-wider">
              <th @click="sort('name')"     class="th px-4 py-3">Joueur      <SortIcon field="name"     :current="sf" :dir="sd" /></th>
              <th @click="sort('team')"     class="th px-4 py-3">Équipe      <SortIcon field="team"     :current="sf" :dir="sd" /></th>
              <th @click="sort('position')" class="th px-4 py-3">Poste       <SortIcon field="position" :current="sf" :dir="sd" /></th>
              <th class="px-4 py-3 text-left text-xs">Club</th>
              <th @click="sort('age')"      class="th px-4 py-3">Âge         <SortIcon field="age"      :current="sf" :dir="sd" /></th>
              <th class="px-4 py-3 text-left text-xs">Né le</th>
              <th class="px-4 py-3 text-right text-xs">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in paginated" :key="p.code + p.name"
                class="border-b border-white/5 hover:bg-white/3 transition-colors group">
              <td class="px-4 py-2.5">
                <div class="flex items-center gap-2.5">
                  <!-- Avatar -->
                  <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 flex items-center justify-center"
                       :class="p.photo ? '' : posBgLight(p.position)">
                    <img v-if="p.photo" :src="p.photo" :alt="p.name"
                         class="w-full h-full object-cover object-top" />
                    <img v-else :src="flagImg(p.code)" :alt="p.code"
                         class="w-full h-full object-cover" />
                  </div>
                  <div>
                    <p class="text-white font-medium">{{ p.name }}</p>
                    <p v-if="p.number" class="text-gray-600 text-[10px]">#{{ p.number }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-2.5 text-gray-400 text-xs whitespace-nowrap">{{ p.team }}</td>
              <td class="px-4 py-2.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase text-white"
                      :class="posBg(p.position)">
                  {{ posShort(p.position) }}
                </span>
              </td>
              <td class="px-4 py-2.5 text-yellow-500/70 text-xs max-w-36 truncate">{{ p.club ?? '—' }}</td>
              <td class="px-4 py-2.5 text-gray-500 text-xs">
                {{ p.birth_date ? calcAge(p.birth_date) + ' ans' : '—' }}
              </td>
              <td class="px-4 py-2.5 text-gray-600 text-xs whitespace-nowrap">{{ p.birth_date ?? '—' }}</td>
              <td class="px-4 py-2.5 text-right">
                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="openEdit(p)"
                          class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400
                                 flex items-center justify-center transition-colors" title="Modifier">
                    <i class="fas fa-pen text-[11px]"></i>
                  </button>
                  <button @click="confirmDelete(p)"
                          class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400
                                 flex items-center justify-center transition-colors" title="Supprimer">
                    <i class="fas fa-trash text-[11px]"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!filtered.length">
              <td colspan="7" class="py-16 text-center text-gray-600">
                <i class="fas fa-users-slash text-3xl block mb-3"></i>
                Aucun joueur — importez d'abord les effectifs
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1"
           class="flex items-center justify-between px-4 py-3 border-t border-white/5">
        <span class="text-gray-600 text-xs">
          Page {{ page }} / {{ totalPages }} · {{ filtered.length }} joueurs
        </span>
        <div class="flex gap-1">
          <button @click="page=1"          :disabled="page===1"          class="pag-btn">«</button>
          <button @click="page--"          :disabled="page===1"          class="pag-btn">‹</button>
          <span class="text-gray-500 text-sm px-2 flex items-center">{{ page }}</span>
          <button @click="page++"          :disabled="page===totalPages" class="pag-btn">›</button>
          <button @click="page=totalPages" :disabled="page===totalPages" class="pag-btn">»</button>
        </div>
      </div>
    </div>

    <!-- ── AUTO-PHOTOS MODAL ────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showFetchModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.85)" @click.self="!fetchRunning && (showFetchModal=false)">
          <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl w-full max-w-lg shadow-2xl">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center">
                  <i class="fas fa-images text-blue-400 text-sm"></i>
                </div>
                <div>
                  <h2 class="text-white font-bold text-sm">Téléchargement automatique des photos</h2>
                  <p class="text-gray-500 text-xs">Sources : TheSportsDB → Wikipedia · Stockage : /storage/players/</p>
                </div>
              </div>
              <button v-if="!fetchRunning" @click="showFetchModal=false"
                      class="w-8 h-8 rounded-lg hover:bg-white/10 text-gray-500 hover:text-white flex items-center justify-center">
                <i class="fas fa-xmark"></i>
              </button>
            </div>

            <div class="p-6 space-y-4">

              <!-- Options -->
              <div v-if="!fetchRunning && !fetchDone" class="space-y-3">
                <label class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 cursor-pointer hover:bg-white/8">
                  <input type="checkbox" v-model="fetchSkipExisting" class="accent-yellow-400" />
                  <div>
                    <p class="text-white text-sm font-medium">Ignorer les photos existantes</p>
                    <p class="text-gray-500 text-xs">Ne télécharge que les joueurs sans photo</p>
                  </div>
                </label>

                <!-- Team filter -->
                <div>
                  <label class="label-admin">Limiter à une équipe (optionnel)</label>
                  <select v-model="fetchTeamFilter" class="input-admin w-full">
                    <option value="">Toutes les équipes ({{ players.length }} joueurs)</option>
                    <option v-for="t in TEAM_OPTIONS" :key="t.fr" :value="t.fr">
                      {{ t.fr }}
                    </option>
                  </select>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3 text-xs text-yellow-400">
                  <i class="fas fa-triangle-exclamation mr-1"></i>
                  {{ fetchTargetCount }} joueurs à traiter · ~{{ Math.ceil(fetchTargetCount * 0.3 / 60) }} min estimées
                </div>
              </div>

              <!-- Progress -->
              <div v-if="fetchRunning || fetchDone" class="space-y-3">
                <!-- Progress bar -->
                <div>
                  <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                    <span>{{ fetchProgress.done }} / {{ fetchProgress.total }}</span>
                    <span>{{ fetchPercent }}%</span>
                  </div>
                  <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full transition-all duration-300"
                         :style="{ width: fetchPercent + '%' }"></div>
                  </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2">
                  <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-2.5 text-center">
                    <p class="text-green-400 font-black text-lg">{{ fetchProgress.found }}</p>
                    <p class="text-gray-600 text-[10px] uppercase tracking-wider">Trouvées</p>
                  </div>
                  <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-2.5 text-center">
                    <p class="text-red-400 font-black text-lg">{{ fetchProgress.notFound }}</p>
                    <p class="text-gray-600 text-[10px] uppercase tracking-wider">Non trouvées</p>
                  </div>
                  <div class="bg-gray-500/10 border border-gray-500/20 rounded-xl p-2.5 text-center">
                    <p class="text-gray-400 font-black text-lg">{{ fetchProgress.skipped }}</p>
                    <p class="text-gray-600 text-[10px] uppercase tracking-wider">Ignorées</p>
                  </div>
                </div>

                <!-- Current player -->
                <div v-if="fetchRunning && fetchCurrentPlayer" class="flex items-center gap-2 text-xs text-gray-500">
                  <i class="fas fa-spinner fa-spin text-blue-400"></i>
                  <span class="truncate">{{ fetchCurrentPlayer }}</span>
                </div>

                <!-- Done -->
                <div v-if="fetchDone" class="flex items-center gap-2 text-sm text-green-400 font-semibold">
                  <i class="fas fa-circle-check"></i>
                  Terminé ! {{ fetchProgress.found }} photos téléchargées.
                </div>

                <!-- Log (last 5) -->
                <div class="bg-black/30 rounded-xl p-3 max-h-32 overflow-y-auto space-y-1">
                  <div v-for="(log, i) in fetchLog.slice(-20)" :key="i"
                       class="text-[11px] flex items-center gap-2"
                       :class="log.status === 'ok' ? 'text-green-400' : log.status === 'existing' ? 'text-gray-600' : 'text-red-400/70'">
                    <i class="fas shrink-0"
                       :class="log.status === 'ok' ? 'fa-check' : log.status === 'existing' ? 'fa-minus' : 'fa-xmark'"></i>
                    <span class="truncate">{{ log.team }} · {{ log.name }}</span>
                    <span v-if="log.status === 'ok'" class="text-gray-700 shrink-0 ml-auto truncate max-w-24">{{ log.url }}</span>
                  </div>
                  <div v-if="!fetchLog.length" class="text-gray-700 text-center">En attente…</div>
                </div>
              </div>

            </div>

            <!-- Footer -->
            <div class="px-6 pb-6 flex gap-3">
              <button v-if="!fetchRunning && !fetchDone"
                      @click="startFetchPhotos"
                      class="btn-admin-primary flex-1 gap-2 text-sm py-3">
                <i class="fas fa-download"></i>
                Lancer le téléchargement
              </button>
              <button v-if="fetchRunning"
                      @click="stopFetch"
                      class="btn-admin-danger flex-1 gap-2 text-sm py-3">
                <i class="fas fa-stop"></i>
                Arrêter
              </button>
              <button v-if="fetchDone"
                      @click="resetFetch"
                      class="btn-admin-primary flex-1 gap-2 text-sm py-3">
                <i class="fas fa-rotate"></i>
                Nouveau lancement
              </button>
              <button v-if="!fetchRunning"
                      @click="showFetchModal=false"
                      class="btn-admin-ghost flex-1 text-sm py-3">
                {{ fetchDone ? 'Fermer' : 'Annuler' }}
              </button>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── PLAYER FORM MODAL ─────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="modal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
             style="background:rgba(0,0,0,.8)" @click.self="modal=false">

          <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl w-full max-w-lg shadow-2xl my-4">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     :class="form.position ? posBg(form.position) : 'bg-white/10'">
                  <i class="fas fa-user text-white text-sm"></i>
                </div>
                <div>
                  <h2 class="text-white font-bold text-sm">
                    {{ isEdit ? 'Modifier le joueur' : 'Ajouter un joueur' }}
                  </h2>
                  <p class="text-gray-500 text-xs">{{ form.name || 'Nouveau joueur' }}</p>
                </div>
              </div>
              <button @click="modal=false"
                      class="w-8 h-8 rounded-lg hover:bg-white/10 text-gray-500 hover:text-white
                             flex items-center justify-center transition-colors">
                <i class="fas fa-xmark"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">

              <!-- Nom + N° -->
              <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                  <label class="label-admin">Nom complet <span class="text-red-400">*</span></label>
                  <input v-model="form.name" class="input-admin w-full"
                         placeholder="Achraf Hakimi"
                         :class="errors.name ? 'border-red-500' : ''" />
                  <p v-if="errors.name" class="text-red-400 text-[11px] mt-1">{{ errors.name }}</p>
                </div>
                <div>
                  <label class="label-admin">N° maillot</label>
                  <input v-model.number="form.number" type="number" min="1" max="99"
                         class="input-admin w-full" placeholder="2" />
                </div>
              </div>

              <!-- Équipe + Position -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label-admin">Équipe <span class="text-red-400">*</span></label>
                  <select v-model="form.team" class="input-admin w-full"
                          :class="errors.team ? 'border-red-500' : ''">
                    <option value="">— Sélectionner —</option>
                    <option v-for="t in TEAM_OPTIONS" :key="t.fr" :value="t.fr">
                      {{ t.fr }}
                    </option>
                  </select>
                  <p v-if="errors.team" class="text-red-400 text-[11px] mt-1">{{ errors.team }}</p>
                </div>
                <div>
                  <label class="label-admin">Position <span class="text-red-400">*</span></label>
                  <div class="grid grid-cols-2 gap-1.5 mt-1">
                    <button v-for="pos in POSITIONS" :key="pos"
                            type="button"
                            @click="form.position = pos"
                            class="py-1.5 rounded-lg text-[11px] font-bold uppercase border transition-colors"
                            :class="form.position === pos
                              ? posBg(pos) + ' border-transparent text-white'
                              : 'border-white/15 text-gray-500 hover:border-white/30 hover:text-gray-300'">
                      {{ posShort(pos) }}
                    </button>
                  </div>
                  <p v-if="errors.position" class="text-red-400 text-[11px] mt-1">{{ errors.position }}</p>
                </div>
              </div>

              <!-- Club + Date naissance -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label-admin">Club actuel</label>
                  <input v-model="form.club" class="input-admin w-full" placeholder="PSG" />
                </div>
                <div>
                  <label class="label-admin">Date de naissance</label>
                  <input v-model="form.birth_date" class="input-admin w-full"
                         placeholder="JJ/MM/AAAA" />
                  <p v-if="form.birth_date" class="text-gray-600 text-[11px] mt-1">
                    {{ calcAge(form.birth_date) }} ans
                  </p>
                </div>
              </div>

              <!-- ── PHOTO ──────────────────────────────────── -->
              <div>
                <label class="label-admin">Photo du joueur</label>

                <!-- Zone principale : grande preview + actions -->
                <div class="flex gap-4 items-stretch">

                  <!-- Drop zone 180×220 (portrait) -->
                  <div class="relative shrink-0 w-44 h-56 rounded-xl overflow-hidden border-2 cursor-pointer
                              transition-all duration-200 group/photo"
                       :class="uploading ? 'border-yellow-400/40 cursor-wait'
                               : dragOver  ? 'border-yellow-400 bg-yellow-400/10 scale-105'
                               : form.photo ? 'border-white/30 shadow-lg shadow-black/40'
                               : 'border-white/15 border-dashed hover:border-white/40 hover:bg-white/3'"
                       @click="!uploading && $refs.photoInput.click()"
                       @dragover.prevent="dragOver=true"
                       @dragleave.prevent="dragOver=false"
                       @drop.prevent="onPhotoDrop">

                    <!-- Photo chargée -->
                    <img v-if="form.photo && !uploading"
                         :src="form.photo"
                         :key="form.photo"
                         class="w-full h-full object-cover object-top"
                         @error="onPhotoError" />

                    <!-- Placeholder -->
                    <div v-else-if="!uploading"
                         class="w-full h-full flex flex-col items-center justify-center gap-2"
                         :class="posBgLight(form.position) || 'bg-white/5'">
                      <i class="fas fa-camera text-gray-500 text-2xl"></i>
                      <span class="text-[10px] text-gray-600 text-center leading-tight px-1">
                        Cliquer ou<br>glisser ici
                      </span>
                    </div>

                    <!-- Spinner upload -->
                    <div v-if="uploading"
                         class="w-full h-full flex flex-col items-center justify-center gap-2 bg-black/60">
                      <i class="fas fa-spinner fa-spin text-yellow-400 text-2xl"></i>
                      <span class="text-[10px] text-yellow-400">Upload…</span>
                    </div>

                    <!-- Hover overlay (changer photo) -->
                    <div v-if="form.photo && !uploading"
                         class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-1
                                opacity-0 group-hover/photo:opacity-100 transition-opacity">
                      <i class="fas fa-camera text-white text-lg"></i>
                      <span class="text-[10px] text-white/80">Changer</span>
                    </div>

                    <!-- Bouton supprimer -->
                    <button v-if="form.photo && !uploading"
                            type="button"
                            @click.stop="removePhoto"
                            class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/80 hover:bg-red-600
                                   text-white flex items-center justify-center transition-colors z-10 shadow">
                      <i class="fas fa-xmark text-[10px]"></i>
                    </button>
                  </div>

                  <!-- Droite : actions + info -->
                  <div class="flex-1 flex flex-col justify-between py-0.5 space-y-2">

                    <!-- Statut photo -->
                    <div>
                      <div v-if="uploading" class="flex items-center gap-2 text-xs text-yellow-400 mb-2">
                        <i class="fas fa-spinner fa-spin"></i> Upload en cours…
                      </div>
                      <div v-else-if="form.photo?.startsWith('/storage/')"
                           class="flex items-center gap-1.5 text-[11px] text-green-400 mb-2 bg-green-500/10
                                  border border-green-500/20 rounded-lg px-2 py-1.5">
                        <i class="fas fa-server shrink-0"></i>
                        <span class="truncate font-mono">{{ form.photo.split('/').pop() }}</span>
                      </div>
                      <div v-else-if="form.photo"
                           class="flex items-center gap-1.5 text-[11px] text-blue-400 mb-2 bg-blue-500/10
                                  border border-blue-500/20 rounded-lg px-2 py-1.5">
                        <i class="fas fa-link shrink-0"></i>
                        <span class="truncate">URL externe</span>
                      </div>
                      <div v-else class="text-[11px] text-gray-600 mb-2">Aucune photo</div>
                    </div>

                    <!-- URL directe -->
                    <div>
                      <p class="text-[10px] text-gray-600 mb-1">URL directe</p>
                      <input v-model="photoUrl"
                             @blur="applyPhotoUrl"
                             @keyup.enter="applyPhotoUrl"
                             placeholder="https://…/photo.jpg"
                             class="input-admin w-full text-xs" />
                    </div>

                    <!-- Bouton fichier -->
                    <button type="button" :disabled="uploading"
                            @click="$refs.photoInput.click()"
                            class="w-full py-2 rounded-lg border border-white/15 text-gray-400
                                   hover:border-yellow-400/50 hover:text-yellow-400 text-xs transition-colors
                                   disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                      <i class="fas fa-upload text-[11px]"></i>
                      Choisir un fichier
                    </button>

                    <p class="text-[10px] text-gray-700">JPG, PNG, WebP · max 2 MB</p>
                  </div>
                </div>

                <!-- Hidden file input -->
                <input ref="photoInput" type="file" accept="image/*"
                       class="hidden" @change="onPhotoFile" />
              </div>

              <!-- Prévisualisation -->
              <div v-if="form.name" class="bg-white/3 border border-white/10 rounded-xl p-3 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 flex items-center justify-center"
                     :class="posBgLight(form.position)">
                  <img v-if="form.photo" :src="form.photo"
                       class="w-full h-full object-cover object-top" />
                  <img v-else :src="flagImg(TEAM_OPTIONS.find(t => t.fr === form.team)?.code ?? '')"
                       class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-white font-semibold text-sm truncate">{{ form.name }}</p>
                  <p class="text-gray-500 text-xs truncate">
                    {{ form.team || '—' }}
                    <span v-if="form.club"> · {{ form.club }}</span>
                  </p>
                </div>
                <span v-if="form.position"
                      class="text-[10px] font-black px-2 py-1 rounded-full uppercase text-white shrink-0"
                      :class="posBg(form.position)">
                  {{ posShort(form.position) }}
                </span>
              </div>

            </div>

            <!-- Footer -->
            <div class="px-6 pb-6 flex gap-3">
              <button @click="save" :disabled="uploading"
                      class="btn-admin-primary flex-1 gap-2 text-sm py-3 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas" :class="uploading ? 'fa-spinner fa-spin' : isEdit ? 'fa-floppy-disk' : 'fa-user-plus'"></i>
                {{ uploading ? 'Upload…' : isEdit ? 'Enregistrer les modifications' : 'Ajouter le joueur' }}
              </button>
              <button @click="modal=false"
                      class="btn-admin-ghost flex-1 text-sm py-3">
                Annuler
              </button>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── DELETE CONFIRM ────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.8)" @click.self="deleteTarget=null">
          <div class="bg-[#0d0d2b] border border-red-500/20 rounded-2xl p-6 w-full max-w-sm">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center">
                <i class="fas fa-trash text-red-400"></i>
              </div>
              <div>
                <p class="text-white font-bold">Supprimer ce joueur ?</p>
                <p class="text-gray-500 text-xs">Cette action est réversible (localStorage).</p>
              </div>
            </div>
            <div class="bg-white/5 rounded-xl p-3 mb-4">
              <p class="text-white font-semibold">{{ deleteTarget.name }}</p>
              <p class="text-gray-500 text-xs mt-0.5 flex items-center gap-1.5">
                <img :src="flagImg(deleteTarget.code)" :alt="deleteTarget.code" class="w-5 h-3.5 object-cover rounded-sm" />
                {{ deleteTarget.team }} · {{ deleteTarget.position }}
              </p>
            </div>
            <div class="flex gap-3">
              <button @click="doDelete"
                      class="flex-1 py-2.5 rounded-xl text-sm font-bold bg-red-600 hover:bg-red-500 text-white transition-colors">
                <i class="fas fa-trash mr-2"></i>Supprimer
              </button>
              <button @click="deleteTarget=null"
                      class="flex-1 btn-admin-ghost text-sm py-2.5">
                Annuler
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast"
             class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl text-sm font-semibold shadow-xl flex items-center gap-2"
             :class="toast.type === 'ok' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
          <i class="fas" :class="toast.type === 'ok' ? 'fa-check' : 'fa-xmark'"></i>
          {{ toast.msg }}
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { onMounted } from 'vue'
import SortIcon from '@/components/SortIcon.vue'
import {
  usePlayers, calcAge, posShort, posBg, posBgLight,
  TEAM_OPTIONS, POSITIONS
} from '@/composables/usePlayers'
import { useAdminStore } from '@/stores/admin'
import { flagImg } from '@/utils/flag'

const PAGE_SIZE = 30

const admin = useAdminStore()
const { players, loadApiClubs, addPlayer, updatePlayer, deletePlayer } = usePlayers()

// ── Filters / sort ───────────────────────────────────────
const search     = ref('')
const teamFilter = ref('')
const posFilter  = ref('')
const page       = ref(1)
const sf         = ref('team')
const sd         = ref('asc')

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  let list = players.value
  if (teamFilter.value) list = list.filter(p => p.team === teamFilter.value)
  if (posFilter.value)  list = list.filter(p => p.position === posFilter.value)
  if (q) list = list.filter(p =>
    p.name.toLowerCase().includes(q) ||
    (p.club ?? '').toLowerCase().includes(q)
  )
  return [...list].sort((a, b) => {
    const va = sf.value === 'age'
      ? (a.birth_date ? calcAge(a.birth_date) : 99)
      : (a[sf.value] ?? '').toString().toLowerCase()
    const vb = sf.value === 'age'
      ? (b.birth_date ? calcAge(b.birth_date) : 99)
      : (b[sf.value] ?? '').toString().toLowerCase()
    return sd.value === 'asc'
      ? (va < vb ? -1 : va > vb ? 1 : 0)
      : (va > vb ? -1 : va < vb ? 1 : 0)
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / PAGE_SIZE)))
const paginated  = computed(() => filtered.value.slice((page.value-1)*PAGE_SIZE, page.value*PAGE_SIZE))

watch([search, teamFilter, posFilter], () => page.value = 1)

function sort(f) {
  if (sf.value === f) sd.value = sd.value === 'asc' ? 'desc' : 'asc'
  else { sf.value = f; sd.value = 'asc' }
}

// ── CRUD ─────────────────────────────────────────────────
const modal        = ref(false)
const isEdit       = ref(false)
const form         = ref(emptyForm())
const errors       = ref({})
const deleteTarget = ref(null)
const toast        = ref(null)
const photoInput   = ref(null)
const photoUrl     = ref('')
const dragOver     = ref(false)
const uploading    = ref(false)
let   _oldPhoto    = null   // photo URL avant édition (pour supprimer si remplacée)

function emptyForm() {
  return { name: '', team: '', position: '', club: '', birth_date: '', number: null, photo: null }
}

function openAdd() {
  form.value   = emptyForm()
  errors.value = {}
  photoUrl.value = ''
  _oldPhoto    = null
  isEdit.value = false
  modal.value  = true
}

function openEdit(p) {
  form.value   = { name: p.name, team: p.team, position: p.position,
                   club: p.club ?? '', birth_date: p.birth_date ?? '',
                   number: p.number ?? null, photo: p.photo ?? null }
  errors.value = {}
  photoUrl.value = (p.photo && !p.photo.startsWith('/storage/')) ? p.photo : ''
  _oldPhoto    = p.photo ?? null
  isEdit.value = true
  modal.value  = true
}

// ── Photo handlers — upload vers /storage/players/ ────────

async function uploadToServer(file) {
  if (file.size > 2 * 1024 * 1024) {
    showToast('Image trop grande (max 2 MB)', 'err')
    return
  }
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('team', form.value.team || 'unknown')
    fd.append('name', form.value.name || 'player')
    const res = await fetch('/api/wc26/players/photo', {
      method: 'POST',
      headers: admin.headers(),
      body: fd,
    })
    if (!res.ok) throw new Error('Upload failed')
    const { url } = await res.json()
    // Supprimer l'ancienne photo serveur si elle existait
    if (_oldPhoto && _oldPhoto.startsWith('/storage/')) {
      deleteServerPhoto(_oldPhoto)
      _oldPhoto = url
    }
    form.value.photo = url
    photoUrl.value   = ''
    showToast('✓ Photo uploadée', 'ok')
  } catch {
    showToast('Erreur upload photo', 'err')
  } finally {
    uploading.value = false
  }
}

function onPhotoFile(e) {
  const file = e.target.files?.[0]
  if (!file) return
  uploadToServer(file)
  e.target.value = ''
}

function onPhotoDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  uploadToServer(file)
}

function applyPhotoUrl() {
  const url = photoUrl.value.trim()
  if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
    form.value.photo = url
  }
}

function removePhoto() {
  if (form.value.photo?.startsWith('/storage/')) {
    deleteServerPhoto(form.value.photo)
  }
  form.value.photo = null
  photoUrl.value   = ''
}

function onPhotoError() {
  // L'image ne charge pas — réinitialiser visuellement sans supprimer l'override
  showToast('⚠ Image inaccessible — vérifiez le proxy ou l\'URL', 'err')
}

function deleteServerPhoto(url) {
  fetch('/api/wc26/players/photo', {
    method: 'DELETE',
    headers: { ...admin.headers(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ url }),
  }).catch(() => {})
}

function validate() {
  errors.value = {}
  if (!form.value.name.trim())     errors.value.name     = 'Nom obligatoire'
  if (!form.value.team)            errors.value.team     = 'Équipe obligatoire'
  if (!form.value.position)        errors.value.position = 'Position obligatoire'
  return Object.keys(errors.value).length === 0
}

function save() {
  if (!validate()) return
  const data = { ...form.value, name: form.value.name.trim() }
  if (isEdit.value) updatePlayer(data)
  else              addPlayer(data)
  modal.value = false
  showToast(isEdit.value ? '✓ Joueur modifié' : '✓ Joueur ajouté', 'ok')
}

function confirmDelete(p) { deleteTarget.value = p }
function doDelete() {
  if (deleteTarget.value.photo?.startsWith('/storage/')) {
    deleteServerPhoto(deleteTarget.value.photo)
  }
  deletePlayer(deleteTarget.value.team, deleteTarget.value.name)
  showToast('Joueur supprimé', 'ok')
  deleteTarget.value = null
}

function showToast(msg, type = 'ok') {
  toast.value = { msg, type }
  setTimeout(() => toast.value = null, 3000)
}

// ── Auto-photos (Wikipedia download) ─────────────────────
const BATCH_SIZE       = 5
const showFetchModal   = ref(false)
const fetchRunning     = ref(false)
const fetchDone        = ref(false)
const fetchSkipExisting = ref(true)
const fetchTeamFilter  = ref('')
const fetchCurrentPlayer = ref('')
const fetchStopFlag    = ref(false)
const fetchLog         = ref([])
const fetchProgress    = ref({ done: 0, total: 0, found: 0, notFound: 0, skipped: 0 })

const fetchTargetPlayers = computed(() => {
  let list = players.value
  if (fetchTeamFilter.value) list = list.filter(p => p.team === fetchTeamFilter.value)
  if (fetchSkipExisting.value) list = list.filter(p => !p.photo)
  return list
})

const fetchTargetCount = computed(() => fetchTargetPlayers.value.length)

const fetchPercent = computed(() => {
  if (!fetchProgress.value.total) return 0
  return Math.round(fetchProgress.value.done / fetchProgress.value.total * 100)
})

function resetFetch() {
  fetchDone.value     = false
  fetchRunning.value  = false
  fetchLog.value      = []
  fetchProgress.value = { done: 0, total: 0, found: 0, notFound: 0, skipped: 0 }
  fetchCurrentPlayer.value = ''
  fetchStopFlag.value = false
}

function stopFetch() { fetchStopFlag.value = true }

async function startFetchPhotos() {
  const targets = fetchTargetPlayers.value
  if (!targets.length) { showToast('Aucun joueur à traiter', 'err'); return }

  fetchRunning.value  = true
  fetchDone.value     = false
  fetchStopFlag.value = false
  fetchLog.value      = []
  fetchProgress.value = { done: 0, total: targets.length, found: 0, notFound: 0, skipped: 0 }

  // Process in batches
  for (let i = 0; i < targets.length; i += BATCH_SIZE) {
    if (fetchStopFlag.value) break

    const batch = targets.slice(i, i + BATCH_SIZE)
    fetchCurrentPlayer.value = batch.map(p => p.name).join(', ')

    try {
      const res = await fetch('/api/wc26/players/fetch-photos', {
        method: 'POST',
        headers: { ...admin.headers(), 'Content-Type': 'application/json' },
        body: JSON.stringify({
          players: batch.map(p => ({ name: p.name, team: p.team, code: p.code })),
          skip_existing: fetchSkipExisting.value,
        }),
      })

      if (!res.ok) throw new Error('HTTP ' + res.status)
      const data = await res.json()

      for (const r of (data.results ?? [])) {
        fetchLog.value.push(r)
        fetchProgress.value.done++

        if (r.status === 'ok') {
          fetchProgress.value.found++
          // Update player override with server URL
          updatePlayer({
            name:       r.name,
            team:       r.team,
            position:   batch.find(p => p.name === r.name)?.position ?? '',
            club:       batch.find(p => p.name === r.name)?.club ?? '',
            birth_date: batch.find(p => p.name === r.name)?.birth_date ?? '',
            number:     batch.find(p => p.name === r.name)?.number ?? null,
            photo:      r.url,
          })
        } else if (r.status === 'existing') {
          fetchProgress.value.skipped++
        } else {
          fetchProgress.value.notFound++
        }
      }
    } catch (err) {
      // Mark batch as errors
      fetchProgress.value.done += batch.length
      fetchProgress.value.notFound += batch.length
      for (const p of batch) {
        fetchLog.value.push({ name: p.name, team: p.team, status: 'not_found', url: null })
      }
    }
  }

  fetchCurrentPlayer.value = ''
  fetchRunning.value = false
  fetchDone.value    = true
  showToast(`✓ ${fetchProgress.value.found} photos téléchargées`, 'ok')
}

onMounted(loadApiClubs)
</script>

<style scoped>
.th { @apply text-left cursor-pointer hover:text-white transition-colors select-none text-xs uppercase tracking-wider; }
.pag-btn { @apply w-8 h-8 rounded-lg bg-white/5 border border-white/10 text-gray-400 disabled:opacity-30 hover:bg-white/10 transition-colors text-sm; }
.modal-enter-active, .modal-leave-active { transition: opacity .2s, transform .2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(.96); }
.toast-enter-active, .toast-leave-active { transition: opacity .3s, transform .3s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }
</style>
