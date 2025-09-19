const messages = {
    languageName: 'Português',
    common: {
        brand: '3D-Print Shop',
        loading: 'A carregar…',
        actions: {
            back: 'Voltar',
            retry: 'Tentar novamente',
        },
        navigation: {
            breadcrumbAria: 'Navegação breadcrumb',
        },
        lightbox: {
            close: 'Fechar',
            prev: 'Imagem anterior',
            next: 'Imagem seguinte',
        },
        toast: {
            close: 'Fechar notificação',
        },
        notFound: {
            seoTitle: ({ brand }: { brand: string }) => `Página não encontrada — 404 — ${brand}`,
            seoDescription: 'Página não encontrada',
            title: '404 — Página não encontrada',
            description: 'A ligação pode estar desatualizada ou ter sido removida.',
            action: 'Voltar ao catálogo',
        },
        errorBoundary: {
            title: 'Ocorreu um erro',
            descriptionFallback: 'Ocorreu um erro inesperado.',
            reload: 'Recarregar',
            home: 'Ir para a página inicial',
        },
    },
    header: {
        brand: '3D-Print Shop',
        nav: {
            catalog: 'Catálogo',
            cookies: 'Gerir cookies',
        },
        account: {
            defaultName: 'A minha conta',
            profile: 'A minha conta',
            logout: 'Terminar sessão',
            login: 'Iniciar sessão',
            register: 'Criar conta',
        },
    },
    consent: {
        ariaLabel: 'Preferências de cookies',
        message: 'Utilizamos cookies para análises (GA4). Clique em «Permitir» para os ativar. Pode alterar a sua escolha a qualquer momento.',
        decline: 'Recusar',
        accept: 'Permitir',
        note: 'Os cookies essenciais não efetuam rastreio. A análise só é ativada com o seu consentimento.',
    },
    search: {
        placeholder: 'Pesquisar produtos…',
        panel: {
            minQuery: ({ min }: { min: number }) => `Introduza pelo menos ${min} caracteres para pesquisar.`,
            loadError: 'Não foi possível carregar as sugestões',
            showAll: ({ query }: { query: string }) => `Mostrar todos os resultados para “${query}”`,
            empty: 'Nenhum resultado encontrado',
        },
    },
    miniCart: {
        summary: {
            total: 'Total',
        },
        actions: {
            viewCart: 'Abrir carrinho',
            checkout: 'Finalizar compra',
        },
        empty: 'O carrinho está vazio',
    },
    recentlyViewed: {
        title: 'Vistos recentemente',
        empty: 'Você ainda não visualizou nenhum produto.',
        noImage: 'sem foto',
    },
    orderChat: {
        title: 'Chat com o vendedor',
        orderLabel: ({ number }: { number: string | number }) => `Pedido ${number}`,
        actions: {
            refresh: 'Atualizar',
            send: 'Enviar',
            sending: 'Enviando…',
        },
        loading: 'A carregar mensagens…',
        empty: 'Ainda não há mensagens. Seja o primeiro a escrever!',
        you: 'Você',
        seller: 'Vendedor',
        inputPlaceholder: 'A sua mensagem para o vendedor…',
        inputHint: {
            maxLength: ({ limit }: { limit: number }) => `Até ${limit} caracteres`,
        },
        guestPrompt: {
            prefix: 'Para enviar uma mensagem ao vendedor,',
            login: 'entre',
            or: 'ou',
            register: 'cadastre-se',
            suffix: '.',
        },
        errors: {
            load: 'Não foi possível carregar as mensagens.',
            send: 'Não foi possível enviar a mensagem.',
        },
    },
    catalog: {
        seo: {
            listName: ({ category }: { category?: string }) => category ? `Catálogo — ${category}` : 'Catálogo',
            documentTitle: ({ category, query }: { category?: string; query?: string }) => {
                const parts = ['Catálogo'];
                if (category) parts.push(category);
                if (query) parts.push(`pesquisa “${query}”`);
                return parts.join(' — ');
            },
            pageTitle: ({ category, query, brand }: { category?: string; query?: string; brand: string }) => {
                const parts = ['Catálogo'];
                if (category) parts.push(category);
                if (query) parts.push(`pesquisa “${query}”`);
                return `${parts.join(' — ')} — ${brand}`;
            },
            description: ({ category, query }: { category?: string; query?: string }) => [
                'Catálogo da loja online. Filtros: categoria, cor, tamanho, preço.',
                category ? `Categoria: ${category}.` : '',
                query ? `Pesquisa: ${query}.` : '',
            ].filter(Boolean).join(' '),
            breadcrumbHome: 'Início',
            breadcrumbCatalog: 'Catálogo',
        },
        header: {
            title: 'Catálogo',
            categoryPlaceholder: 'Categoria',
            allCategories: 'Todas as categorias',
            sort: {
                new: 'Novidades',
                priceAsc: 'Preço ↑',
                priceDesc: 'Preço ↓',
            },
        },
        filters: {
            searchPlaceholder: 'Pesquisar produtos…',
            priceMinPlaceholder: 'Preço desde',
            priceMaxPlaceholder: 'até',
            applyPrice: 'Aplicar',
            clearAll: 'Limpar tudo',
            active: {
                color: ({ value }: { value: string }) => `Cor: ${value}`,
                size: ({ value }: { value: string }) => `Tamanho: ${value}`,
                minPrice: ({ value }: { value: number }) => `Desde: ${value}`,
                maxPrice: ({ value }: { value: number }) => `Até: ${value}`,
                clearTooltip: 'Limpar este filtro',
                clearAll: 'Limpar tudo',
            },
            facets: {
                categories: 'Categorias',
                colors: 'Cor',
                sizes: 'Tamanho',
                empty: 'sem dados',
            },
        },
        products: {
            empty: 'Nada encontrado. Tente ajustar os filtros.',
        },
        cards: {
            noImage: 'sem imagem',
            outOfStock: 'Indisponível',
            adding: 'A comprar…',
            addToCart: 'Comprar',
        },
        pagination: {
            prev: 'Anterior',
            next: 'Seguinte',
            pageStatus: ({ page, lastPage }: { page: number; lastPage: number }) => `Página ${page} de ${lastPage}`,
        },
    },
    sellerPage: {
        pageTitle: ({ name }: { name?: string }) => name ? `${name} — Vendedor` : 'Vendedor',
        documentTitle: ({ name, brand }: { name?: string; brand: string }) =>
            name ? `${name} — Vendedor — ${brand}` : `Vendedor — ${brand}`,
        productsTitle: 'Produtos do vendedor',
        loadingVendor: 'A carregar informações do vendedor…',
        notFound: 'Vendedor não encontrado.',
        noProducts: 'Este vendedor ainda não tem produtos disponíveis.',
        noImage: 'sem imagem',
        contact: {
            email: ({ email }: { email: string }) => `Email: ${email}`,
            phone: ({ phone }: { phone: string }) => `Telefone: ${phone}`,
        },
        seo: {
            title: ({ name, brand }: { name?: string; brand: string }) =>
                name ? `${name} — Vendedor — ${brand}` : `Vendedor — ${brand}`,
            description: ({ description, email, phone }: { description?: string; email?: string; phone?: string }) => {
                const parts = [
                    description?.trim() ?? '',
                    email ? `Email: ${email}` : '',
                    phone ? `Telefone: ${phone}` : '',
                ].filter(Boolean);
                return parts.length ? parts.join(' ') : 'Página do vendedor. Contactos e produtos.';
            },
        },
        pagination: {
            prev: 'Anterior',
            next: 'Seguinte',
            status: ({ page, lastPage }: { page: number; lastPage: number }) => `Página ${page} de ${lastPage}`,
        },
        errors: {
            loadProducts: 'Não foi possível carregar os produtos do vendedor.',
        },
        ga: {
            listName: ({ name }: { name: string }) => `Vendedor ${name}`,
        },
    },
    profile: {
        navigation: {
            overview: 'Perfil',
            orders: 'As minhas encomendas',
            addresses: 'Endereços guardados',
            points: 'Pontos de fidelidade',
        },
        overview: {
            loading: 'A carregar o perfil…',
            title: 'Perfil',
            welcome: ({ name }: { name: string }) =>
                `Bem-vindo, ${name}. Gere os seus dados e explore as outras secções do perfil.`,
            guestName: 'utilizador',
            personalDataTitle: 'Dados pessoais',
            verification: {
                title: 'O email não está verificado.',
                description: 'Verifique a sua caixa de entrada ou volte a enviar o email de confirmação.',
                resend: {
                    sending: 'A enviar…',
                    action: 'Reenviar email de confirmação',
                },
                successFallback: 'Email de confirmação reenviado.',
                errorFallback: 'Não foi possível enviar o email de confirmação. Tente novamente.',
            },
            form: {
                labels: {
                    name: 'Nome',
                    email: 'Email',
                    newPassword: 'Nova palavra-passe',
                    confirmPassword: 'Confirmar palavra-passe',
                },
                placeholders: {
                    name: 'Introduza o nome',
                    email: 'your@email.com',
                    newPassword: 'Deixe em branco para manter',
                    confirmPassword: 'Repita a nova palavra-passe',
                },
                hintPasswordOptional:
                    'Pode deixar a palavra-passe em branco se não a quiser alterar. O email deve ser único.',
                hintApplyImmediately: 'As alterações entram em vigor imediatamente após guardar.',
                submit: {
                    saving: 'A guardar…',
                    save: 'Guardar alterações',
                },
            },
            info: {
                id: 'ID',
                name: 'Nome',
                email: 'Email',
                verified: 'Email verificado',
                verifiedYes: 'Sim',
                verifiedNo: 'Não',
            },
            session: {
                tokenNote: 'O token Sanctum é guardado localmente para pedidos autenticados à API.',
                logout: {
                    processing: 'A terminar sessão…',
                    action: 'Terminar sessão',
                    error: 'Não foi possível terminar a sessão. Tente novamente.',
                },
            },
            notifications: {
                updateSuccess: 'Dados do perfil atualizados.',
            },
            errors: {
                update: 'Não foi possível atualizar o perfil. Tente novamente.',
                loadTwoFactorStatus: 'Não foi possível carregar o estado da autenticação de dois fatores.',
                startTwoFactor: 'Não foi possível iniciar a configuração da autenticação de dois fatores.',
                confirmTwoFactor: 'Não foi possível confirmar o código. Tente novamente.',
                disableTwoFactor: 'Não foi possível desativar a autenticação de dois fatores.',
                resendVerification: 'Não foi possível enviar o email de confirmação. Tente novamente.',
            },
            twoFactor: {
                title: 'Autenticação de dois fatores',
                statusLabel: 'Estado:',
                status: {
                    enabled: 'Ativa',
                    pending: 'A aguardar confirmação',
                    disabled: 'Desativada',
                },
                confirmedAtLabel: 'Confirmado:',
                description: 'A autenticação de dois fatores adiciona uma camada extra de segurança à sua conta.',
                loadingStatus: 'A carregar estado…',
                secret: {
                    title: 'Chave secreta',
                    instructions:
                        'Adicione esta chave à sua aplicação de autenticação (Google Authenticator, 1Password, Authy, etc.). Também pode abrir a configuração diretamente através da ligação abaixo.',
                    openApp: 'Abrir na aplicação',
                },
                confirm: {
                    codeLabel: 'Código de confirmação',
                    codePlaceholder: 'Introduza o código da aplicação',
                    helper: 'Introduza o código de seis dígitos da aplicação de autenticação para concluir a configuração.',
                    submit: 'Confirmar',
                    submitting: 'A confirmar…',
                    cancel: 'Cancelar',
                },
                callouts: {
                    pendingSetup: 'A configuração anterior não foi concluída. Pode gerar uma nova chave secreta para recomeçar.',
                },
                enable: {
                    action: 'Ativar 2FA',
                    loading: 'Aguarde…',
                },
                disable: {
                    action: 'Desativar 2FA',
                    confirm: 'Tem a certeza de que pretende desativar a autenticação de dois fatores?',
                },
                messages: {
                    enabled: 'Autenticação de dois fatores ativada.',
                    disabled: 'Autenticação de dois fatores desativada.',
                    emptyCode: 'Introduza o código de confirmação da aplicação.',
                },
            },
        },
        orders: {
            loading: 'A carregar encomendas…',
            title: 'As minhas encomendas',
            description: 'Consulte o histórico de compras, acompanhe o estado das encomendas e abra os detalhes.',
            error: 'Não foi possível carregar as encomendas.',
            table: {
                loading: 'A carregar…',
                empty: {
                    description: 'Ainda não efetuou nenhuma encomenda.',
                    cta: 'Ver o catálogo',
                },
                headers: {
                    number: 'Número',
                    date: 'Data',
                    status: 'Estado',
                    total: 'Total',
                    actions: 'Ações',
                },
                view: 'Ver detalhes da encomenda',
            },
        },
        addresses: {
            loading: 'A carregar endereços…',
            title: 'Endereços guardados',
            description: 'Gira os endereços de entrega para acelerar as próximas compras.',
            error: 'Não foi possível carregar os endereços.',
            list: {
                loading: 'A carregar…',
                empty: 'Ainda não tem endereços guardados. Adicione um durante a finalização da compra.',
                defaultName: 'Sem título',
                fields: {
                    city: 'Cidade',
                    address: 'Endereço',
                    postalCode: 'Código postal',
                    phone: 'Telefone',
                },
            },
        },
        points: {
            loading: 'A carregar pontos…',
            title: 'Pontos de fidelidade',
            description: 'Acompanhe o saldo disponível e o histórico de utilização dos pontos.',
            error: 'Não foi possível carregar a informação de pontos.',
            type: {
                default: 'Movimento',
                earn: 'Acumulação',
                redeem: 'Utilização',
            },
            stats: {
                balance: 'Disponível',
                earned: 'Acumulado',
                spent: 'Utilizado',
            },
            table: {
                loading: 'A carregar…',
                empty:
                    'Ainda não existe histórico de pontos. Utilize-os durante a compra para ver aqui a atividade.',
                headers: {
                    date: 'Data',
                    description: 'Descrição',
                    type: 'Tipo',
                    amount: 'Quantidade',
                },
                type: {
                    default: 'Movimento',
                    earn: 'Acumulação',
                    redeem: 'Utilização',
                },
            },
        },
    },
    cart: {
        seoTitle: ({ brand }: { brand: string }) => `Carrinho — ${brand}`,
        title: 'Carrinho',
        loading: 'A carregar…',
        empty: {
            message: 'O carrinho está vazio.',
            cta: 'Ir às compras',
        },
        vendor: {
            label: 'Vendedor',
            contact: 'Contactar o vendedor',
        },
        line: {
            remove: 'Remover',
        },
        summary: {
            totalLabel: 'Total',
            total: 'A pagar',
            checkout: 'Finalizar compra',
        },
    },
    checkout: {
        seoTitle: ({ brand }: { brand: string }) => `Finalização da compra — ${brand}`,
        title: 'Finalização da compra',
        steps: {
            address: 'Endereço',
            delivery: 'Entrega',
            payment: 'Pagamento',
        },
        notifications: {
            cartUnavailable: 'O carrinho está vazio ou já foi finalizado.',
            cartCheckFailed: 'Não foi possível verificar o carrinho.',
            addressesLoadFailed: 'Não foi possível carregar os endereços.',
            couponApplyFailed: 'Não foi possível aplicar o cupão.',
            couponApplied: 'Cupão aplicado.',
            couponRemoved: 'Cupão removido.',
            orderCreateSuccess: 'Encomenda criada. Conclua o pagamento.',
            orderCreateFailed: 'Não foi possível criar a encomenda.',
        },
        address: {
            emailLabel: 'Email de contacto',
            emailPlaceholder: 'you@example.com',
            saved: {
                title: 'Endereços guardados',
                emptyAuthenticated: 'Ainda não tem endereços guardados.',
                emptyGuest: 'Inicie sessão para usar endereços guardados.',
            },
            fields: {
                name: {
                    label: 'Nome do destinatário',
                    placeholder: 'Nome e apelido',
                },
                city: {
                    label: 'Cidade',
                    placeholder: 'Kyiv',
                },
                addr: {
                    label: 'Endereço de entrega',
                    placeholder: 'Rua Shevchenko, 1',
                },
                postal: {
                    optionalLabel: 'Código postal (opcional)',
                    placeholder: '01001',
                },
                phone: {
                    optionalLabel: 'Telefone (opcional)',
                    placeholder: '+380 00 000 0000',
                },
            },
            next: 'Continuar para entrega',
        },
        billing: {
            toggle: 'Necessito de dados para faturação',
            description: 'Indique os dados de faturação para recibos e documentos.',
            copyFromShipping: 'Copiar da entrega',
            fields: {
                name: {
                    label: 'Nome / pessoa de contacto',
                    placeholder: 'Nome e apelido',
                },
                company: {
                    label: 'Empresa (opcional)',
                    placeholder: 'Exemplo Lda.',
                },
                taxId: {
                    label: 'Número fiscal (NIF / VAT)',
                    placeholder: '123456789',
                },
                city: {
                    label: 'Cidade',
                    placeholder: 'Kyiv',
                },
                addr: {
                    label: 'Endereço de faturação',
                    placeholder: 'Rua Shevchenko, 1',
                },
                postal: {
                    optionalLabel: 'Código postal (opcional)',
                    placeholder: '01001',
                },
            },
        },
        errors: {
            emailRequired: 'Indique um email para confirmação.',
            emailInvalid: 'Indique um email válido.',
            shippingNameRequired: 'Indique o nome do destinatário.',
            shippingCityRequired: 'Indique a cidade de entrega.',
            shippingAddrRequired: 'Indique o endereço de entrega.',
            billingNameRequired: 'Indique o nome para faturação.',
            billingCityRequired: 'Indique a cidade para faturação.',
            billingAddrRequired: 'Indique o endereço para faturação.',
            billingTaxRequired: 'Indique o número fiscal da empresa.',
        },
        delivery: {
            title: 'Método de entrega',
            commentLabel: 'Nota para o estafeta (opcional)',
            commentPlaceholder: 'Por exemplo, telefonar 30 minutos antes da entrega',
            options: {
                nova: {
                    title: 'Nova Poshta',
                    description: 'Entrega na Ucrânia em 2–3 dias.',
                },
                ukr: {
                    title: 'Ukrposhta',
                    description: 'Entrega económica 3–5 dias para a loja.',
                },
                pickup: {
                    title: 'Levantamento',
                    description: 'Levante a encomenda hoje na nossa oficina (Kiev).',
                },
            },
        },
        coupon: {
            title: 'Cupão',
            placeholder: 'Introduza o código do cupão',
            applying: 'A aplicar…',
            apply: 'Aplicar',
            applied: ({ code }: { code: string }) => `Cupão aplicado: ${code}`,
        },
        summary: {
            title: 'A sua encomenda',
            quantity: ({ count }: { count: number }) => `Quantidade: ${count}`,
            subtotal: 'Subtotal dos artigos',
            discount: 'Desconto',
            total: 'A pagar',
            notice: 'Depois de avançar para o pagamento terá de criar uma nova encomenda para alterar o endereço ou a entrega.',
            goToPayment: 'Avançar para pagamento',
            creating: 'A criar…',
        },
        payment: {
            preparing: 'A preparar o pagamento…',
            orderNumberLabel: 'Número da encomenda',
            confirmationNotice: ({ email }: { email: string }) => `A confirmação será enviada para ${email}.`,
            totalNotice: ({ amount }: { amount: string }) => `Valor a pagar: ${amount}`,
            title: 'Pagamento',
            description: 'Pagamento seguro via Stripe. Após uma transação bem-sucedida será redirecionado para a confirmação da encomenda.',
            billingTitle: 'Dados de faturação',
            billingTax: ({ taxId }: { taxId: string }) => `Número fiscal: ${taxId}`,
            billingMatchesShipping: 'Os dados de faturação são iguais aos da entrega.',
            shippingTitle: 'Entrega',
            shippingMethod: ({ method }: { method: string }) => `Método de entrega: ${method}`,
            shippingComment: ({ comment }: { comment: string }) => `Comentário: ${comment}`,
            itemsTitle: 'Artigos',
        },
        notes: {
            delivery: ({ method }: { method: string }) => `Entrega: ${method}`,
            comment: ({ comment }: { comment: string }) => `Comentário: ${comment}`,
        },
    },
    order: {
        confirmation: {
            loading: 'A carregar…',
            notFound: 'Encomenda não encontrada.',
            seoTitle: ({ number, brand }: { number: string; brand: string }) => `Encomenda ${number} — ${brand}`,
            title: ({ number }: { number: string }) => `Obrigado! A encomenda ${number} foi confirmada`,
            confirmationNotice: ({ email }: { email: string }) => `A confirmação foi enviada para ${email}.`,
            paymentPending: 'Pagamento pendente.',
            chat: {
                open: 'Contactar o vendedor',
                close: 'Ocultar chat',
            },
            shipping: {
                title: 'Envio e seguimento',
                trackingNumber: 'Número de seguimento:',
                pending: 'Pendente',
            },
            billing: {
                title: 'Dados de faturação',
                taxIdLabel: 'Número fiscal:',
            },
            table: {
                product: 'Produto',
                quantity: 'Qtd',
                price: 'Preço',
                total: 'Total',
                viewProduct: 'Ver produto',
                vendor: 'Vendedor:',
                contactSeller: 'Contactar o vendedor',
                subtotal: 'Total de produtos',
                coupon: 'Cupão',
                discount: 'Desconto',
                loyalty: 'Pontos usados',
                loyaltyValue: ({ amount }: { amount: string }) => `(−${amount})`,
                amountDue: 'Total a pagar',
            },
            cta: {
                continue: 'Continuar a comprar',
            },
            payment: {
                title: 'Pagamento da encomenda',
                description: 'Seguro via Stripe. Cartões e métodos locais (UE) disponíveis.',
            },
        },
    },
    auth: {
        shared: {
            loading: 'A carregar…',
            processing: 'Aguarde…',
        },
        register: {
            title: 'Registo',
            nameLabel: 'Nome',
            emailLabel: 'Email',
            passwordLabel: 'Palavra-passe',
            passwordConfirmationLabel: 'Confirmar palavra-passe',
            submit: 'Criar conta',
            haveAccount: 'Já tem conta?',
            signInLink: 'Iniciar sessão',
            passwordMismatch: 'As palavras-passe não coincidem.',
            errorFallback: 'Não foi possível concluir o registo. Tente novamente.',
        },
        login: {
            title: 'Iniciar sessão',
            emailLabel: 'Email',
            passwordLabel: 'Palavra-passe',
            forgotPassword: 'Esqueceu-se da palavra-passe?',
            submit: 'Iniciar sessão',
            noAccount: 'Não tem conta?',
            registerLink: 'Registar-se',
            errorFallback: 'Não foi possível iniciar sessão. Tente novamente.',
            otpRequired: 'É necessário um código único. Introduza o código da aplicação de autenticação.',
            otpLabel: 'Código de verificação',
            otpPlaceholder: 'Por exemplo, 123456',
            otpHelp: 'Use a sua aplicação de autenticação para obter um código de seis dígitos.',
        },
        reset: {
            fields: {
                emailLabel: 'Email',
                passwordLabel: 'Nova palavra-passe',
                passwordConfirmationLabel: 'Confirmar palavra-passe',
            },
            errors: {
                emailRequired: 'Introduza o email.',
                emailInvalid: 'Introduza um endereço de email válido.',
                passwordRequired: 'Introduza a nova palavra-passe.',
                passwordTooShort: 'A palavra-passe deve ter pelo menos 8 caracteres.',
                confirmationRequired: 'Confirme a nova palavra-passe.',
                passwordMismatch: 'As palavras-passe não coincidem.',
            },
            shared: {
                backToLogin: 'Voltar ao início de sessão',
            },
            request: {
                title: 'Recuperar palavra-passe',
                description: 'Introduza o email e enviaremos uma ligação para redefinir a palavra-passe.',
                submit: 'Enviar ligação',
                submitting: 'A enviar…',
                remember: 'Lembra-se da palavra-passe?',
                successFallback: 'Ligação para redefinir a palavra-passe enviada.',
                errorFallback: 'Não foi possível enviar o email. Tente novamente.',
            },
            update: {
                title: 'Definir nova palavra-passe',
                description: 'Preencha os dados para definir uma nova palavra-passe para a conta.',
                submit: 'Alterar palavra-passe',
                submitting: 'A guardar…',
                successFallback: 'Palavra-passe atualizada. Já pode iniciar sessão.',
                errorFallback: 'Não foi possível atualizar a palavra-passe. Verifique os dados e tente novamente.',
                backToLoginPrefix: 'Voltar à',
                backToLoginLink: 'página de início de sessão',
                backToLoginSuffix: '.',
            },
        },
    },
    wishlist: {
        badge: 'Lista de desejos',
        title: 'Lista de desejos',
        clear: 'Limpar',
        loading: 'A atualizar a sua lista de desejos…',
        errorTitle: 'Não foi possível atualizar a lista',
        errors: {
            auth: 'Inicie sessão para sincronizar a lista de desejos.',
            sync: 'Não foi possível sincronizar a lista de desejos.',
            partialSync: 'Alguns artigos não foram sincronizados com a lista de desejos.',
        },
        empty: 'Ainda está vazio.',
        button: {
            add: 'Adicionar à lista de desejos',
            remove: 'Na lista de desejos',
            addAria: 'Adicionar à lista de desejos',
            removeAria: 'Remover da lista de desejos',
        },
        removeAria: ({ name }: { name: string }) => `Remover “${name}” da lista de desejos`,
        noImage: 'sem imagem',
    },
    product: {
        seo: {
            pageTitle: ({ name, price, brand }: { name: string; price: string; brand: string }) => `${name} — ${price} — ${brand}`,
            fallbackTitle: ({ brand }: { brand: string }) => `Produto — ${brand}`,
            description: ({ name, price, inStock }: { name: string; price: string; inStock: boolean }) =>
                `Compre ${name} por ${price}. ${inStock ? 'Disponível.' : 'Sem stock.'} Faça o pedido online.`,
            fallbackDescription: 'Página do produto na loja.',
            breadcrumbHome: 'Início',
            breadcrumbCatalog: 'Catálogo',
        },
        gallery: {
            noImage: 'sem foto',
            openImage: ({ index }: { index: number }) => `Abrir imagem ${index}`,
        },
        reviews: {
            ariaLabel: 'Avaliações',
            title: 'Avaliações',
            summary: {
                loading: 'A carregar a classificação…',
                label: 'Classificação média:',
                of: ({ max }: { max: number }) => `de ${max}`,
                empty: 'Ainda não há avaliações',
            },
            loading: 'A carregar avaliações…',
            empty: 'Ainda não há avaliações. Seja o primeiro!',
            anonymous: 'Cliente',
            ratingLabel: ({ value, max }: { value: number; max: number }) => `${value} de ${max}`,
            pending: 'A avaliação aguarda moderação. Avisaremos após a publicação.',
        },
        stock: {
            available: ({ count }: { count: number }) => `Disponível: ${count} un.`,
            unavailable: 'Sem stock',
        },
        actions: {
            addToCart: 'Adicionar ao carrinho',
            backToCatalog: '← Voltar ao catálogo',
        },
        toasts: {
            added: {
                title: 'Adicionado ao carrinho',
                action: 'Abrir carrinho',
                error: 'Não foi possível adicionar ao carrinho',
            },
        },
        tabs: {
            description: 'Descrição',
            specs: 'Especificações',
            delivery: 'Entrega',
        },
        description: {
            empty: 'A descrição ainda não está disponível.',
        },
        specs: {
            empty: 'As especificações ainda não foram adicionadas.',
        },
        delivery: {
            items: {
                novaPoshta: 'Nova Poshta na Ucrânia — 1–3 dias.',
                courier: 'Entrega por correio nas grandes cidades — 1–2 dias.',
                payment: 'Pagamento: cartão online ou pagamento na entrega.',
                returns: 'Devolução/troca — 14 dias (de acordo com a lei de defesa do consumidor).',
            },
        },
        ratingStars: {
            option: ({ value, max }: { value: number; max: number }) => `Classificação ${value} de ${max}`,
            hint: ({ value, max }: { value: number; max: number }) => `${value} de ${max}`,
        },
        similar: {
            title: 'Produtos semelhantes',
            empty: 'Ainda não há produtos semelhantes.',
            noImage: 'Sem foto',
            count: ({ count }: { count: number }) => `Encontrados semelhantes: ${count}`,
        },
        reviewForm: {
            ariaLabel: 'Formulário de avaliação',
            title: 'Deixar uma avaliação',
            authPromptPrefix: 'Para partilhar a sua experiência,',
            authPromptLogin: 'inicie sessão',
            authPromptMiddle: 'ou',
            authPromptRegister: 'registe-se',
            authPromptSuffix: '.',
            formErrorUnauthenticated: 'Para deixar uma avaliação, inicie sessão na sua conta.',
            successTitle: 'Obrigado pela sua avaliação!',
            successDescription: 'A sua avaliação será publicada após moderação.',
            errorFallback: 'Não foi possível enviar a avaliação. Tente mais tarde.',
            errorTitle: 'Não foi possível enviar a avaliação',
            ratingLabel: 'Classificação',
            commentLabel: 'Comentário (opcional)',
            commentPlaceholder: 'Partilhe a sua experiência com o produto',
            submitting: 'A enviar…',
            submit: 'Enviar avaliação',
        },
    },
    notify: {
        cart: {
            add: {
                success: 'Adicionado ao carrinho',
                action: 'Abrir carrinho',
                outOfStock: 'Stock insuficiente',
                error: 'Não foi possível adicionar ao carrinho',
            },
            update: {
                success: 'Quantidade atualizada',
                outOfStock: 'Stock insuficiente',
                error: 'Falha ao atualizar o carrinho',
            },
            remove: {
                success: 'Removido do carrinho',
            },
        },
    },
} as const;

export default messages;
