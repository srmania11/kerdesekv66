        <div class="q_v_answers_section">

            <style>
                .restricted-content-container {
                    position: relative;
                    margin-top: 20px;
                    border-radius: 12px;
                    overflow: hidden; /* Ensure blur doesn't bleed */
                }
                .restricted-blur-area {
                    filter: blur(8px);
                    -webkit-filter: blur(8px);
                    opacity: 0.6;
                    pointer-events: none;
                    user-select: none;
                    transition: all 0.5s ease;
                }
                .restricted-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    justify-content: center;
                    align-items: center; /* Center vertically in the block */
                    z-index: 20;
                }
                .login-card {
                    background: rgba(255, 255, 255, 0.75);
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    padding: 40px 30px;
                    border-radius: 24px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    text-align: center;
                    border: 1px solid rgba(255, 255, 255, 0.5);
                    max-width: 90%;
                    width: 480px;
                    animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                }
                @keyframes fadeUp {
                    from { opacity: 0; transform: translateY(40px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                /* Button styles override/addition */
                .btn-glass-primary {
                    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
                    color: white;
                    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
                }
                .btn-glass-primary:hover {
                    box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
                    transform: translateY(-2px);
                }
                .btn-glass-secondary {
                    background: rgba(255, 255, 255, 0.8);
                    color: #1e293b;
                    border: 1px solid rgba(203, 213, 225, 0.6);
                }
                .btn-glass-secondary:hover {
                    background: #fff;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                }

                /* Answer Image Styles */
                .q_v_attached_image_container {
                    margin-top: 15px;
                    padding-top: 15px;
                    border-top: 1px dashed #e2e8f0;
                }
                .q_v_attached_label {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 0.85rem;
                    font-weight: 700;
                    color: #64748b;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    white-space: nowrap;
                }
                .q_v_attached_link {
                    display: block;
                    width: 100%;
                    max-width: 400px; /* Desktop limit */
                    border-radius: 8px;
                    overflow: hidden;
                    border: 1px solid #cbd5e1;
                    transition: transform 0.2s, box-shadow 0.2s;
                    background: #f8fafc;
                }
                .q_v_attached_link:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }
                .q_v_attached_img {
                    display: block;
                    width: 100%;
                    height: auto;
                    object-fit: contain;
                }
                @media (max-width: 768px) {
                    .q_v_attached_image_container {
                        width: 100% !important;
                        box-sizing: border-box !important;
                    }
                    .q_v_attached_link {
                        max-width: 100% !important;
                        width: 100% !important;
                        min-width: 100% !important;
                        display: block !important;
                        box-sizing: border-box !important;
                    }
                    .q_v_attached_img {
                        width: 100% !important;
                        min-width: 100% !important;
                        max-width: none !important;
                        height: auto !important;
                        min-height: auto !important;
                        object-fit: contain !important;
                        display: block !important;
                        box-sizing: border-box !important;
                    }
                }
            </style>

            <div class="q_v_answers_header_modern" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div class="q_v_answers_count" style="font-size:1.2rem; font-weight:700; color:#334155;">
                    <?php echo $total_answers; ?> Válasz
                </div>
                <div class="q_v_controls_modern" style="display:flex; gap:10px;">
                    <!-- Limit Dropdown -->
                    <div class="q_v_dropdown" style="position:relative;">
                        <button class="q_v_dd_btn" onclick="toggleDropdown('limitDropdown', event)" style="padding:8px 12px; background:white; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; color:#475569; display:flex; align-items:center; gap:5px;">
                            <i class="fa-solid fa-layer-group"></i>
                            <span class="q_v_btn_text"><?php echo $limit; ?> / oldal</span>
                            <i class="fa-solid fa-chevron-down" style="font-size:0.8em; opacity:0.7;"></i>
                        </button>
                        <div id="limitDropdown" class="q_v_dd_menu" style="display:none; position:absolute; top:100%; right:0; background:white; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); border-radius:4px; min-width:120px; z-index:50; margin-top:5px;">
                            <a href="#" onclick="changeLimit(5, event)" style="display:block; padding:8px 12px; text-decoration:none; color:#334155; <?php echo $limit==5?'background:#f1f5f9; font-weight:bold;':''; ?>">5 válasz</a>
                            <a href="#" onclick="changeLimit(10, event)" style="display:block; padding:8px 12px; text-decoration:none; color:#334155; <?php echo $limit==10?'background:#f1f5f9; font-weight:bold;':''; ?>">10 válasz</a>
                            <a href="#" onclick="changeLimit(15, event)" style="display:block; padding:8px 12px; text-decoration:none; color:#334155; <?php echo $limit==15?'background:#f1f5f9; font-weight:bold;':''; ?>">15 válasz</a>
                        </div>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="q_v_dropdown" style="position:relative;">
                        <button class="q_v_dd_btn" onclick="toggleDropdown('sortDropdown', event)" style="padding:8px 12px; background:white; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer; color:#475569; display:flex; align-items:center; gap:5px;">
                            <?php if($sort === 'newest'): ?>
                                <i class="fa-solid fa-clock"></i> <span class="q_v_btn_text">Legújabb</span>
                            <?php else: ?>
                                <i class="fa-solid fa-star"></i> <span class="q_v_btn_text">Legjobb</span>
                            <?php endif; ?>
                            <i class="fa-solid fa-chevron-down" style="font-size:0.8em; opacity:0.7;"></i>
                        </button>
                        <div id="sortDropdown" class="q_v_dd_menu" style="display:none; position:absolute; top:100%; right:0; background:white; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); border-radius:4px; min-width:160px; z-index:50; margin-top:5px;">
                             <a href="#" onclick="changeSort('best', event)" style="display:block; padding:8px 12px; text-decoration:none; color:#334155; display:flex; align-items:center; gap:8px; <?php echo $sort=='best'?'background:#f1f5f9; font-weight:bold;':''; ?>">
                                <i class="fa-solid fa-star" style="color:#eab308;"></i> Legjobbra értékelt
                             </a>
                             <a href="#" onclick="changeSort('newest', event)" style="display:block; padding:8px 12px; text-decoration:none; color:#334155; display:flex; align-items:center; gap:8px; <?php echo $sort=='newest'?'background:#f1f5f9; font-weight:bold;':''; ?>">
                                <i class="fa-solid fa-clock" style="color:#64748b;"></i> Legújabb elöl
                             </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $lock_started = false;
            foreach ($answers as $index => $answer):
                // Logic for locking content
                $should_blur = (!$currentUser && (($page > 1) || ($index >= 5)));

                if ($should_blur && !$lock_started):
                    $lock_started = true;
            ?>
                <div class="restricted-content-container">
                    <div class="restricted-overlay">
                        <div class="login-card">
                            <div class="mb-6">
                                <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 mb-2 shadow-inner">
                                    <i class="fa-solid fa-lock text-2xl"></i>
                                </span>
                            </div>
                            <h3 class="text-2xl font-extrabold text-gray-800 mb-3 tracking-tight">További válaszok megtekintése</h3>
                            <p class="text-gray-600 mb-8 text-base leading-relaxed">
                                A tartalom további része és a beszélgetés teljes egésze csak regisztrált felhasználóink számára érhető el.
                                <br><span class="text-sm font-medium text-indigo-500 mt-2 block">Csatlakozz közösségünkhöz ingyenesen!</span>
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center w-full">
                                <a href="login.php" class="btn-glass-primary flex-1 py-3 px-6 font-bold rounded-xl transition-all duration-300 flex items-center justify-center gap-2 no-underline">
                                    <i class="fa-solid fa-right-to-bracket"></i> Bejelentkezés
                                </a>
                                <a href="register.php" class="btn-glass-secondary flex-1 py-3 px-6 font-bold rounded-xl transition-all duration-300 flex items-center justify-center gap-2 no-underline">
                                    <i class="fa-solid fa-user-plus"></i> Regisztráció
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="restricted-blur-area">
            <?php endif; ?>

                <?php
                // Check if this answer is being edited
                $isEditingThis = (isset($_GET['edit_answer']) && $_GET['edit_answer'] == $answer['id'] && $currentUser && ($currentUser['user_id'] == $answer['user_id'] || !empty($currentUser['is_admin'])));
                ?>
            <div class="q_v_answer_card <?php echo $answer['is_accepted'] ? 'q_v_answer_solution' : ''; ?> <?php echo $answer['is_ai'] ? 'q_v_answer_ai' : ''; ?> <?php echo $isEditingThis ? 'q_v_answer_editing' : ''; ?>" id="answer-card-<?php echo $answer['id']; ?>">

                <?php if ($isEditingThis): ?>
                    <!-- EDIT MODE -->
                    <div class="q_v_editor_wrapper">
                        <h3 style="font-size:1.1rem; margin-bottom:10px;">Válasz szerkesztése</h3>
                        <form method="POST" action="question.php?id=<?php echo $question_id; ?>&page=<?php echo $page; ?>">
                            <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                            <input type="hidden" name="page" value="<?php echo $page; ?>">

                            <div class="q_v_editor_content" id="editAnswerEditor-<?php echo $answer['id']; ?>" contenteditable="true" style="border:1px solid #ccc; min-height: 150px; padding: 10px; background:white; color:black; margin-bottom:10px;"><?php echo $answer['content']; ?></div>
                            <input type="hidden" name="content" id="editAnswerContent-<?php echo $answer['id']; ?>">

                            <div class="q_v_edit_buttons">
                                <button type="submit" name="update_answer" class="q_v_btn q_v_btn_primary" onclick="document.getElementById('editAnswerContent-<?php echo $answer['id']; ?>').value = document.getElementById('editAnswerEditor-<?php echo $answer['id']; ?>').innerHTML;">Mentés</button>
                                <a href="question.php?id=<?php echo $question_id; ?>&page=<?php echo $page; ?>#answer-card-<?php echo $answer['id']; ?>" class="q_v_btn q_v_btn_secondary">Mégse</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- NORMAL MODE -->
                    <?php if ($answer['is_accepted']): ?>
                        <div class="q_v_solution_banner"><i class="fa-solid fa-check"></i> Megoldás</div>
                    <?php endif; ?>

                    <div class="q_v_vote_box" id="vote-box-<?php echo $answer['id']; ?>">
                        <?php if ($answer['is_ai']): ?>
                            <button class="q_v_vote_btn" disabled style="opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-caret-up"></i></button>
                            <span class="q_v_vote_count"><?php echo $answer['vote_score']; ?></span>
                            <button class="q_v_vote_btn" disabled style="opacity:0.5; cursor:not-allowed;"><i class="fa-solid fa-caret-down"></i></button>
                        <?php else: ?>
                            <button class="q_v_vote_btn js-vote-btn" data-type="answer" data-id="<?php echo $answer['id']; ?>" data-dir="up"><i class="fa-solid fa-caret-up"></i></button>
                            <span class="q_v_vote_count <?php echo ($answer['vote_score'] < 0) ? 'text-red-500' : ''; ?>" style="<?php echo $answer['is_accepted'] ? 'color: #10b981;' : ''; ?>" id="vote-score-answer-<?php echo $answer['id']; ?>"><?php echo $answer['vote_score']; ?></span>
                            <button class="q_v_vote_btn js-vote-btn" data-type="answer" data-id="<?php echo $answer['id']; ?>" data-dir="down"><i class="fa-solid fa-caret-down"></i></button>
                        <?php endif; ?>
                    </div>

                    <div class="q_v_answer_main" id="answer-main-<?php echo $answer['id']; ?>">
                        <div class="q_v_answer_meta">
                            <div class="q_v_answer_user">
                                <?php if ($answer['is_ai']): ?>
                                    <div class="q_v_answer_avatar" style="background:#eff6ff; display:flex; align-items:center; justify-content:center; color:#3b82f6; font-size:1.2rem;">
                                        <i class="fa-solid fa-robot"></i>
                                    </div>
                                    <span class="q_v_user_name" style="font-size: 0.95rem; color:#3b82f6;">Mesterséges Intelligencia</span>
                                    <span style="color:#64748b; font-size: 0.8rem;">• Automatikus</span>
                                <?php else: ?>
                                    <?php
                                    $avatar_bg = $answer['is_accepted'] ? '10b981' : 'random';
                                    $ans_username = $answer['username'] ?: ($answer['guest_name'] ?: 'Vendég');
                                    ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($ans_username); ?>&background=<?php echo $avatar_bg; ?>&color=fff" class="q_v_answer_avatar">

                                    <span class="q_v_user_name" style="font-size: 0.95rem; cursor: default;"><?php echo htmlspecialchars($ans_username); ?></span>
                                    <span style="color:#64748b; font-size: 0.8rem;">• <?php echo htmlspecialchars($answer['custom_title'] ?? 'Tag'); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="q_v_meta_actions">
                                <span style="color:#94a3b8; font-size: 0.8rem;"><?php echo time_elapsed_string($answer['created_at']); ?></span>

                                <?php if (!empty($answer['edit_count']) && $answer['edit_count'] > 0): ?>
                                    <span class="text-xs text-gray-400 italic hidden md:inline" title="<?php echo $answer['edit_count']; ?> szerkesztés">
                                        <i class="fa-solid fa-pencil"></i> Szerkesztve
                                    </span>
                                    <button class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline bg-transparent border-none cursor-pointer p-0 hidden md:inline" onclick="loadAnswerHistory(<?php echo $answer['id']; ?>)">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Előzmények
                                    </button>
                                <?php endif; ?>

                                <?php if ($currentUser && ($currentUser['user_id'] == $answer['user_id'] || !empty($currentUser['is_admin'])) && $question['status'] !== 'solved' && $question['status'] !== 'closed'): ?>
                                    <div class="q_v_ans_actions">
                                        <a href="question.php?id=<?php echo $question_id; ?>&edit_answer=<?php echo $answer['id']; ?>&page=<?php echo $page; ?>#answer-card-<?php echo $answer['id']; ?>" class="q_v_ans_btn" title="Szerkesztés"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" onsubmit="return confirm('Biztosan törlöd a választ? A kapott pontok levonásra kerülnek!');" style="display:inline;">
                                            <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                                            <button type="submit" name="delete_answer" class="q_v_ans_btn q_v_ans_btn_del" title="Törlés"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <?php if ($currentUser && $currentUser['user_id'] != $answer['user_id']): ?>
                                    <a href="tartalom_jelentese.php?type=answer&id=<?php echo $answer['id']; ?>"
                                    style="color:#cbd5e1; font-size:0.85rem; text-decoration:none;"
                                    title="Jelentés"
                                    onmouseover="this.style.color='#ef4444'"
                                    onmouseout="this.style.color='#cbd5e1'">
                                        <i class="fa-solid fa-flag"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="q_v_text">
                            <?php echo process_content_for_display($answer['content'], $currentUser); ?>
                        </div>

                        <?php if (!empty($answer['attached_image'])): ?>
                            <div class="q_v_attached_image_container">
                                <span class="q_v_attached_label"><i class="fa-solid fa-paperclip"></i> Csatolt fénykép</span>
                                <a href="<?php echo htmlspecialchars($answer['attached_image']['image_path']); ?>" target="_blank" class="q_v_attached_link">
                                    <img src="<?php echo htmlspecialchars($answer['attached_image']['image_path']); ?>" alt="Csatolt kép" class="q_v_attached_img">
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($answer['is_ai']): ?>
                            <div class="q_v_ai_disclaimer">
                                <i class="fa-solid fa-robot"></i> Ezt a választ AI generálta és hibázhat, ezért mindig tájékozódjon tovább.
                            </div>
                        <?php endif; ?>

                        <?php if ($currentUser && $question['user_id'] == $currentUser['user_id']): ?>
                            <div class="q_v_accept_container">
                                <?php if ($answer['is_accepted']): ?>
                                    <button class="q_v_btn q_v_btn_secondary js-accept-btn" data-id="<?php echo $answer['id']; ?>" style="font-size:0.8rem; border-color:#10b981; color:#10b981;">
                                        <i class="fa-solid fa-check"></i> Megoldás visszavonása
                                    </button>
                                <?php else: ?>
                                    <button class="q_v_btn q_v_btn_secondary js-accept-btn" data-id="<?php echo $answer['id']; ?>" style="font-size:0.8rem;">
                                        <i class="fa-solid fa-check"></i> Megoldásnak jelölöm
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- REPLY BUTTON & MOBILE ACTIONS -->
                        <div class="mt-4 border-t border-gray-100 pt-3 flex justify-end items-center gap-4" style="grid-column: 1 / -1; width: 100%;">
                            <!-- Mobile Edit/History (Aligned to the left of Reply button on mobile) -->
                            <div class="flex items-center gap-4 md:hidden">
                                <?php if (!empty($answer['edit_count']) && $answer['edit_count'] > 0): ?>
                                    <span class="text-xs text-gray-400 italic flex items-center gap-1">
                                        <i class="fa-solid fa-pencil"></i> Szerkesztve
                                    </span>
                                    <button class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline bg-transparent border-none cursor-pointer p-0 flex items-center gap-1" onclick="loadAnswerHistory(<?php echo $answer['id']; ?>)">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Előzmények
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Reply Button -->
                            <button onclick="loadAnswerReplies(<?php echo $answer['id']; ?>)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-2 px-3 py-1.5 rounded hover:bg-indigo-50 whitespace-nowrap">
                                <i class="fa-solid fa-comments"></i>
                                <?php if (isset($answer['reply_count']) && $answer['reply_count'] > 0): ?>
                                    <?php echo $answer['reply_count']; ?> válasz
                                <?php else: ?>
                                    Válasz
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>

                    <!-- History Container -->
                    <div class="q_v_answer_history hidden" id="answer-history-<?php echo $answer['id']; ?>" style="flex:1; padding:20px; min-width:0;"></div>

                    <!-- Replies Container -->
                    <div class="q_v_answer_replies hidden" id="answer-replies-<?php echo $answer['id']; ?>" style="flex:1; padding:20px; min-width:0; grid-column: 1 / -1; width: 100%;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if ($lock_started): ?>
                    </div> <!-- End restricted-blur-area -->
                </div> <!-- End restricted-content-container -->
            <?php endif; ?>

        </div>

        <!-- LAPOZÁS -->
        <?php if ($total_pages > 1): ?>
        <div class="q_v_pagination">
            <?php if ($page > 1): ?>
                <a href="#" onclick="changePage(<?php echo $page - 1; ?>, event)" class="q_v_page_item"><i class="fa-solid fa-chevron-left"></i> Előző</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="#" onclick="changePage(<?php echo $i; ?>, event)" class="q_v_page_item <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="#" onclick="changePage(<?php echo $page + 1; ?>, event)" class="q_v_page_item">Következő <i class="fa-solid fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
