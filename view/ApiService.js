/**
 * Classe ApiService para comunicação com a API PHP (MVC + JWT)
 * Adaptada para a estrutura: aplicativo, usuario, veiculo
 * 
 * Endpoints disponíveis:
 * - POST /login (público)
 * - GET/POST/PUT/DELETE /aplicativos (protegido)
 * - GET/POST/PUT/DELETE /usuarios (protegido)  
 * - GET/POST/PUT/DELETE /veiculos (protegido)
 */
export default class ApiService {
    #token;
    #baseURL;

    /**
     * Construtor da classe ApiService.
     * @param {string|null} token - Token JWT de autenticação
     * @param {string} baseURL - URL base da API (padrão para XAMPP)
     */
    constructor(token = null, baseURL = 'http://localhost/api') {
        this.#token = token;
        this.#baseURL = baseURL.endsWith('/') ? baseURL.slice(0, -1) : baseURL;
    }

    /**
     * Constrói a URL completa
     */
    #buildURL(endpoint) {
        const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
        return `${this.#baseURL}${cleanEndpoint}`;
    }

    /**
     * Constrói os headers padrão
     */
    #buildHeaders(additionalHeaders = {}) {
        const headers = {
            "Content-Type": "application/json",
            ...additionalHeaders
        };

        if (this.#token) {
            headers["Authorization"] = `Bearer ${this.#token}`;
        }

        return headers;
    }

    /**
     * Processa a resposta da API
     */
    async #processResponse(response, url, method) {
        try {
            const contentType = response.headers.get("content-type");
            let jsonObj = null;

            if (contentType && contentType.includes("application/json")) {
                jsonObj = await response.json();
            } else if (response.status === 204) {
                jsonObj = { success: true, message: "Operação realizada com sucesso" };
            }

            console.log(`${method}:`, url, jsonObj);

            // Trata erro de autenticação (token inválido/expirado)
            if (response.status === 401) {
                this.#handleAuthError();
                return { success: false, error: { message: "Token inválido ou expirado" } };
            }

            if (!response.ok) {
                const errorMessage = jsonObj?.error?.message || jsonObj?.message || `Erro HTTP: ${response.status}`;
                return {
                    success: false,
                    error: {
                        message: errorMessage,
                        status: response.status,
                        code: jsonObj?.error?.code
                    }
                };
            }

            return jsonObj || { success: true };

        } catch (error) {
            console.error(`Erro ao processar resposta ${method}:`, error.message);
            return { success: false, error: { message: "Erro ao processar resposta do servidor" } };
        }
    }

    /**
     * Trata erros de autenticação
     */
    #handleAuthError() {
        localStorage.removeItem("userData");
        if (typeof window !== 'undefined' && window.location) {
            console.warn("Token inválido - redirecionando para login");
            window.location.href = "login.html";
        }
    }

    /**
     * GET simples sem autenticação
     */
    async simpleGet(endpoint) {
        try {
            const url = this.#buildURL(endpoint);
            const response = await fetch(url);
            return await this.#processResponse(response, url, 'GET');
        } catch (error) {
            console.error("Erro ao buscar dados:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * GET com autenticação
     */
    async get(endpoint, additionalHeaders = {}) {
        try {
            const url = this.#buildURL(endpoint);
            const headers = this.#buildHeaders(additionalHeaders);

            const response = await fetch(url, {
                method: "GET",
                headers: headers
            });

            return await this.#processResponse(response, url, 'GET');
        } catch (error) {
            console.error("Erro ao buscar dados:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * GET por ID
     */
    async getById(endpoint, id, additionalHeaders = {}) {
        try {
            const fullEndpoint = `${endpoint}/${id}`;
            const url = this.#buildURL(fullEndpoint);
            const headers = this.#buildHeaders(additionalHeaders);

            const response = await fetch(url, {
                method: "GET",
                headers: headers
            });

            return await this.#processResponse(response, url, 'GET BY ID');
        } catch (error) {
            console.error("Erro ao buscar por ID:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * POST
     */
    async post(endpoint, jsonObject, additionalHeaders = {}) {
        try {
            const url = this.#buildURL(endpoint);
            const headers = this.#buildHeaders(additionalHeaders);

            const response = await fetch(url, {
                method: "POST",
                headers: headers,
                body: JSON.stringify(jsonObject)
            });

            return await this.#processResponse(response, url, 'POST');
        } catch (error) {
            console.error("Erro ao enviar dados:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * PUT
     */
    async put(endpoint, id, jsonObject, additionalHeaders = {}) {
        try {
            const fullEndpoint = `${endpoint}/${id}`;
            const url = this.#buildURL(fullEndpoint);
            const headers = this.#buildHeaders(additionalHeaders);

            const response = await fetch(url, {
                method: "PUT",
                headers: headers,
                body: JSON.stringify(jsonObject)
            });

            return await this.#processResponse(response, url, 'PUT');
        } catch (error) {
            console.error("Erro ao atualizar dados:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * DELETE
     */
    async delete(endpoint, id, additionalHeaders = {}) {
        try {
            const fullEndpoint = `${endpoint}/${id}`;
            const url = this.#buildURL(fullEndpoint);
            const headers = this.#buildHeaders(additionalHeaders);

            console.log("DELETE:", url);
            const response = await fetch(url, {
                method: "DELETE",
                headers: headers
            });

            return await this.#processResponse(response, url, 'DELETE');
        } catch (error) {
            console.error("Erro ao deletar dados:", error.message);
            return { success: false, error: { message: "Erro de conectividade" } };
        }
    }

    /**
     * Método específico para login
     */
    async login(email, senha) {
        const loginData = {
            usuario: {
                email: email,
                senha: senha
            }
        };
        return await this.post('login', loginData);
    }

    /**
     * Verifica se tem token
     */
    hasToken() {
        return !!this.#token;
    }

    /**
     * Verifica se está logado
     */
    static isLoggedIn() {
        try {
            const userData = localStorage.getItem("userData");
            if (!userData) return false;

            const parsed = JSON.parse(userData);
            return parsed && parsed.success === true && parsed.data && parsed.data.token;
        } catch (error) {
            return false;
        }
    }

    /**
     * Faz logout
     */
    static logout() {
        localStorage.removeItem("userData");
        if (typeof window !== 'undefined' && window.location) {
            window.location.href = "login.html";
        }
    }

    /**
     * Obtém dados do usuário logado
     */
    static getUserData() {
        try {
            const userData = localStorage.getItem("userData");
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            return null;
        }
    }

    // Getters e Setters
    get token() {
        return this.#token;
    }

    set token(value) {
        this.#token = value;
    }

    get baseURL() {
        return this.#baseURL;
    }

    set baseURL(value) {
        this.#baseURL = value.endsWith('/') ? value.slice(0, -1) : value;
    }
}