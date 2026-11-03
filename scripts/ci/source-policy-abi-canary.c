/* Container ONLY. Fixed no-destination, nonblocking syscall-entry controls.
 * Number source: libseccomp f0b04ab0 src/syscalls.csv, arch-x86.c/arch-x32.c.
 * PNR aliases have NO invented direct number; i386 socketcall covers them.
 */
#include "source-policy-abi-core.h"
static const int routes[][4]={
    {41,359,41,1},{53,360,53,8},{42,362,42,3},{49,361,49,2},
    {50,363,50,4},{43,-1,43,5},{288,364,288,18},{48,373,48,13},
    {51,367,51,6},{52,368,52,7},{54,366,541,14},{55,365,542,15},
    {-1,-1,-1,9},{44,369,44,11},{46,370,518,16},{307,345,538,20},
    {-1,-1,-1,10},{45,371,517,12},{47,372,519,17},{299,337,537,19},
    {-1,417,-1,-1}
};
static u32 args[6],pair[2],buffer[32];
static long direct(int abi,long n) {
    if(abi==1)return call32(n,args[0],args[1],args[2],args[3],args[4]);
    return call64(n|(abi==2?0x40000000:0), (int)args[0],args[1],args[2],args[3],args[4],args[5]);
}
static void arguments(int sub) {
    for(int i=0;i<6;i++)args[i]=0;
    args[0]=0xffffffff;args[1]=(u32)(unsigned long)buffer;args[2]=1;
    if(sub==1){args[0]=2;args[1]=2|2048;args[2]=0;} /* private AF_INET datagram */
    if(sub==8){args[0]=1;args[1]=1|2048;args[2]=0;args[3]=(u32)(unsigned long)pair;} /* unnamed pair */
}
static int equal(const char *a,const char *b) {
    for(int i=0;i<8;i++){if(a[i]!=b[i])return 0;if(!a[i])return 1;}return 0;
}
long body(long *stack) {
    if(stack[0]!=2)return 90;
    const char *name=(const char *)stack[2];
    int abi=equal(name,"amd64")?0:equal(name,"i386")?1:equal(name,"x32")?2:-1;
    if(abi<0)return 90;
    state();
    number(direct(abi,abi==1?20:39)); /* getpid ignores stale zero-initialized args */
    for(int i=0;i<21;i++) {
        int n=routes[i][abi],sub=routes[i][3];
        if(n<0)continue;
        arguments(sub);long out=direct(abi,n);number(out);
        if(sub==1&&out>=0)call64(3,out,0,0,0,0,0);
        if(sub==8&&out==0){call64(3,pair[0],0,0,0,0,0);call64(3,pair[1],0,0,0,0,0);}
    }
    if(abi==1)for(int sub=1;sub<=20;sub++) {
        arguments(sub);long out=call32(102,sub,(long)args,0,0,0);number(out);
        if(sub==1&&out>=0)call64(3,out,0,0,0,0,0);
        if(sub==8&&out==0){call64(3,pair[0],0,0,0,0,0);call64(3,pair[1],0,0,0,0,0);}
    }
    number(direct(abi,abi==1?20:39)); /* must continue after ALL probes */
    return 0;
}
