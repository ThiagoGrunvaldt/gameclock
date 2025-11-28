import sys
import json
import mysql.connector
import collections

def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="game_clock"
    )

def get_user_games(cursor, user_id):
    cursor.execute("SELECT appid FROM jogos WHERE usuario_id = %s", (user_id,))
    return {row[0] for row in cursor.fetchall()}

def get_all_other_users(cursor, main_user_id):
    cursor.execute("SELECT id FROM usuarios WHERE id != %s", (main_user_id,))
    return [row[0] for row in cursor.fetchall()]

if __name__ == "__main__":
    main_user_id = int(sys.argv[1])
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    jogos_usuario_x = get_user_games(cursor, main_user_id)
    outros_usuarios_ids = get_all_other_users(cursor, main_user_id)
    
    similaridade_scores = []
    
    # --- PASSO 1: Rankear todos os outros usuários
    for user_id in outros_usuarios_ids:
        jogos_outro_usuario = get_user_games(cursor, user_id)
        jogos_em_comum = jogos_usuario_x.intersection(jogos_outro_usuario)
        score = len(jogos_em_comum)
        
        if score > 0:
            similaridade_scores.append( (score, user_id) )
    
    # Ordena pelos "melhor rankeado" (maior score)
    similaridade_scores.sort(reverse=True)
    
    # --- PASSO 2: Selecionar os 5 usuários mais similares
    top_5_similares_tuplas = similaridade_scores[:5]
    top_5_similares_ids = [user_id for score, user_id in top_5_similares_tuplas]

    # --- PASSO 3: Encontrar os 5 jogos mais comuns ENTRE ELES
    
    # Se não houver usuários similares, retorna lista vazia
    if not top_5_similares_ids:
        print(json.dumps([]))
        cursor.close()
        conn.close()
        sys.exit()

    # Cria uma lista de TODOS os jogos possuídos pelos 5 usuários mais similares
    lista_de_jogos_dos_similares = []
    for user_id in top_5_similares_ids:
        jogos_do_similar = get_user_games(cursor, user_id)
        lista_de_jogos_dos_similares.extend(list(jogos_do_similar))

    # Conta a frequência de cada jogo nessa lista
    contagem_de_jogos = collections.Counter(lista_de_jogos_dos_similares)
    
    # Pega todos os jogos mais comuns, ordenados pela contagem
    jogos_mais_comuns_ordenados = contagem_de_jogos.most_common() 
    
    recomendacoes = []
    
    # --- PASSO 4: Filtrar e selecionar os 5 melhores
    
    for appid, contagem in jogos_mais_comuns_ordenados:
        # Verifica se o usuário X AINDA NÃO TEM o jogo
        if appid not in jogos_usuario_x:
            recomendacoes.append(appid)
            
        if len(recomendacoes) >= 5:
            break
            
    cursor.close()
    conn.close()
    
    print(json.dumps(recomendacoes))