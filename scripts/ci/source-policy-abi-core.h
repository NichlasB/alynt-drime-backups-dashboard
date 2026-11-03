/* Owned no-libc Linux/amd64 fixture. No file, socket or process-creation API. */
typedef unsigned int u32;
static long call64(long n,long a,long b,long c,long d,long e,long f) {
    register long r10 __asm__("r10")=d, r8 __asm__("r8")=e, r9 __asm__("r9")=f;
    long out;
    __asm__ volatile("syscall":"=a"(out):"a"(n),"D"(a),"S"(b),"d"(c),"r"(r10),"r"(r8),"r"(r9):"rcx","r11","memory","cc");
    return out;
}
static long call32(long n,long a,long b,long c,long d,long e) {
    long out;
    __asm__ volatile("push %%rbp; xor %%ebp,%%ebp; int $0x80; pop %%rbp"
        :"=a"(out):"a"(n),"b"(a),"c"(b),"d"(c),"S"(d),"D"(e):"r8","r9","r10","r11","memory","cc");
    return (long)(int)out;
}
static char line[64];
static void number(long n) {
    unsigned long v=n<0?(unsigned long)(-n):(unsigned long)n;
    int end=63,start=end; line[end]='\n';
    do {line[--start]=(char)('0'+v%10);v/=10;} while(v);
    if(n<0)line[--start]='-';
    if(call64(1,1,(long)(line+start),end-start+1,0,0,0)!=end-start+1)call64(60,91,0,0,0,0,0);
}
static void state(void) {
    number(call64(102,0,0,0,0,0,0)); /* getuid */
    number(call64(157,39,0,0,0,0,0)); /* PR_GET_NO_NEW_PRIVS */
    number(call64(157,21,0,0,0,0,0)); /* PR_GET_SECCOMP */
}
__asm__(".global _start\n_start:\nmov %rsp,%rdi\nandq $-16,%rsp\ncall body\nmov %rax,%rdi\nmov $60,%rax\nsyscall\n");
