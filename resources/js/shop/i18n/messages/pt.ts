const messages = {
    languageName: 'Português',
    common: {
        brand: '3D-Print Shop',
        loading: 'A carregar…',
        actions: {
            back: 'Voltar',
            retry: 'Tentar novamente',
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
    },
    wishlist: {
        title: 'Lista de desejos',
        clear: 'Limpar',
        loading: 'A atualizar a sua lista de desejos…',
        errorTitle: 'Não foi possível atualizar a lista',
        empty: 'Ainda está vazio.',
        removeAria: ({ name }: { name: string }) => `Remover “${name}” da lista de desejos`,
        noImage: 'sem imagem',
    },
    product: {
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
