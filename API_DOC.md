# Estrutura da Coleção Postman/Insomnia: API de Conteúdo

Esta documentação cobre todas as rotas CRUD (Criar, Listar, Visualizar, Deletar) e as rotas de Transição de Estado (`/aprovar` e `/reprovar`) do projeto.

## Variável de Ambiente

- **{{BASE_URL}}**  
    Defina uma variável de ambiente chamada `BASE_URL` ou `HOST` configurada como:  
    `http://localhost:8000/api` (ou a URL do seu servidor de teste).

---

## 1. Criar Novo Conteúdo

- **Método:** `POST`
- **URL:** `{{BASE_URL}}/conteudos`
- **Descrição:** Cria um novo registro de conteúdo com status inicial `escrito`.

### Corpo da Requisição (JSON)

```json
{
    "papel": "admin",
    "conteudo": "Esboço inicial para a campanha de Natal."
}
```

### Resposta Esperada

- **Status:** `201 Created`
- **Exemplo:**

```json
{
    "papel": "admin",
    "conteudo": "Esboço inicial para a campanha de Natal.",
    "status": "escrito",
    "motivo_reprovacao": null,
    "updated_at": "...",
    "created_at": "...",
    "id": 1
}
```

---

## 2. Listar Conteúdos com Filtros/Paginação

- **Método:** `GET`
- **URL:** `{{BASE_URL}}/conteudos`
- **Descrição:** Retorna a lista de conteúdos. Suporta filtros opcionais e paginação.

### Exemplos de URL

- Simples:  
    `{{BASE_URL}}/conteudos`
- Com filtros:  
    `{{BASE_URL}}/conteudos?status=escrito&papel=redator&per_page=5`

### Resposta Esperada

- **Status:** `200 OK`  
    (Inclui metadados de paginação)

---

## 3. Visualizar Conteúdo

- **Método:** `GET`
- **URL:** `{{BASE_URL}}/conteudos/{ID_DO_CONTEUDO}`
- **Descrição:** Retorna um conteúdo específico pelo seu ID.

### Respostas Esperadas

- **Sucesso:** `200 OK`
- **Não encontrado:** `404 Not Found`

---

## 4. Aprovar Conteúdo

- **Método:** `POST`
- **URL:** `{{BASE_URL}}/conteudos/{ID_DO_CONTEUDO}/aprovar`
- **Descrição:** Muda o status de `escrito` para `aprovado`.

### Respostas Esperadas

- **Sucesso:** `200 OK`

```json
{
    // ... outros campos
    "status": "aprovado",
    "motivo_reprovacao": null
}
```

- **Falha/Transição Inválida:** `400 Bad Request`

---

## 5. Reprovar Conteúdo

- **Método:** `POST`
- **URL:** `{{BASE_URL}}/conteudos/{ID_DO_CONTEUDO}/reprovar`
- **Descrição:** Muda o status de `escrito` para `reprovado`. Exige o campo `motivo`.

### Corpo da Requisição (JSON)

```json
{
    "motivo": "O texto está muito superficial, precisa de mais dados estatísticos."
}
```

### Respostas Esperadas

- **Sucesso:** `200 OK`
- **Motivo ausente/validação:** `422 Unprocessable Entity`

---

## 6. Deletar Conteúdo

- **Método:** `DELETE`
- **URL:** `{{BASE_URL}}/conteudos/{ID_DO_CONTEUDO}`
- **Descrição:** Remove o conteúdo do banco de dados (e registra o log de auditoria).

### Resposta Esperada

- **Status:** `204 No Content`